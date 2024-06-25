#!/usr/bin/env ruby
# encoding: utf-8
$KCODE = 'UTF8' unless RUBY_VERSION > "1.9.0"

FONT_SIZE = 12
EMPTY_CHAR = Array.new(FONT_SIZE, 0x0)
OUTPUT_FOLDER='generated/'


def skip_until(f, regex)
  while(not f.eof?)
    l = f.readline().chomp
    ret = regex.match(l)
    if ret
      return ret
    end
  end
  return false
end

class Glyph
  attr_reader :code, :is_full, :data

  def initialize(c, f, d)
    raise "type error" unless c.instance_of? Fixnum
    raise "type error" unless d.instance_of? Array

    @code = c
    @is_full = f
    @data = d
  end
end


def read_font(f, half, encoding)
  code = skip_until(f, /STARTCHAR\s+(\w+)/)
  if not code
    return false
  else
    c = code[1].hex
    if encoding == "JIS_X0208"
      encoding = "CP932"

      j1 = (c >> 8) & 0xff
      j2 = c & 0xff

      s1 = (32 < j1 and j1 <= 94) ? (j1 + 1) / 2 + 112 : (j1 + 1) / 2 + 176
      s2 = (j1 & 0x01) == 1 ? j2 + 31 + j2 / 96 : j2 + 126

      c = ((s1 & 0xff) << 8) + s2
      code = [s1, s2].pack("CC")
    elsif encoding == "UTF-32LE"
      code = [c].pack("L<")
    else
      code = (c < 0x100 ? [c] : [c & 0xff, (c >> 8) & 0xff]).pack("C*")
    end

    raise "size error" unless code.bytesize == (c < 0x100 ? 1 : 2) || encoding == "UTF-32LE"

    begin
      code = code.force_encoding(encoding).encode('UTF-32LE')
    rescue Encoding::InvalidByteSequenceError
      print "invalid code 0x%02x, 0x%02x\n" % [(c >> 8) & 0xff, c & 0xff]
      return false
    end

    raise "invalid code" unless code.bytesize == 4

    code = code.unpack("V")[0]
  end

  width = half ? FONT_SIZE / 2 : FONT_SIZE

  skip_until(f, /BITMAP/)

  ret = Array.new(FONT_SIZE, 0)

  for y in 0...FONT_SIZE
    str = f.readline()

    tmp = 0
    for x in 0...width
      if str[x] == ?@
        tmp |= 0x01 << x
      else
        raise "assert" unless str[x] == ?.
      end
    end
    ret[y] = tmp
  end

  raise "assert" unless /ENDCHAR/.match(f.readline().encode('UTF-8', 'EUC-JP'))

  return Glyph.new(code, !half, ret)
end

def read_file(f, encoding, half)
  ret = {}
  while(true)
    font = read_font(f, half, encoding)
    if f.eof?
      return ret
    elsif font
      ret[font.code] = font
    end
  end
end

def write_sql(f, id, code, title, data)
  if data.size < 1 then
    f.write <<EOS
INSERT INTO fonts(id, code, name, frozen)
VALUES (#{id}, '#{code}', '#{title}', 0);
EOS
    return
  end

  f.write <<EOS
INSERT INTO fonts(id, code, name, frozen)
VALUES (#{id}, '#{code}', '#{title}', 1);

INSERT INTO glyphs(char_code, adder_ip, verified, font_id, added_at, is_active, is_fullwidth, data)
VALUES
EOS

  is_first = true
  data.sort.each { |v|
    g = v[1]
    arr_2bytes = g.data.map do |n|
      ([n].pack('S').unpack('CC').map { |byte| '%02x' % byte }).join('')
    end
    str_bytes = arr_2bytes.join('')
    f.write "," unless is_first
    is_first = false
    f.write "\n(#{g.code}, NULL, 0, #{id}, 0, 1, #{g.is_full}, X'#{str_bytes}')"
  }
  f.write ";\n\n\n"
end


fonts = [
  {id: :latin, title: 'Latin-1', file: './fonts/shinonome/latin1/font_src.bit', codepage: 'ISO-8859-1', halfwidth: true},
  {id: :latin_ext_a, title: 'Latin Extended A', file: './fonts/shinonome/latin-ext-a/font_src.bit', codepage: 'UTF-32LE', halfwidth: true},
  {id: :extras, title: 'Extras', file: './fonts/shinonome/extras/font_src.bit', codepage: 'UTF-32LE', halfwidth: true},
  {id: :extras_fullwidth, file: './fonts/shinonome/extras/font_src.bit', codepage: 'UTF-32LE', halfwidth: true},
  {id: :hankaku, title: 'Hankaku', file: './fonts/shinonome/hankaku/font_src_diff.bit', codepage: 'CP932', halfwidth: true},
  {id: :gothic, title: 'Gothic', file: './fonts/shinonome/kanjic/font_src.bit', codepage: 'JIS_X0208', halfwidth: false},
  {id: :mincho, title: 'Mincho', file: './fonts/shinonome/mincho/font_src_diff.bit', codepage: 'JIS_X0208', halfwidth: false},
  {id: :korean, title: 'Korean', file: './fonts/shinonome/korean/font_src_diff.bit', codepage: 'UTF-32LE', halfwidth: false},
  {id: :chinese, title: 'Chinese', file: './fonts/shinonome/chinese/font_src_diff.bit', codepage: 'UTF-32LE', halfwidth: false},
  {id: :rmg2000, title: 'RMG2000-compatible', file: './fonts/shinonome/rmg2000/font_src.bit', codepage: 'UTF-32LE', halfwidth: true},
  {id: :ttyp0, title: 'ttyp0 (RM2000 replacement)', file: './fonts/ttyp0/font.bit', codepage: 'UTF-32LE', halfwidth: true},
]

output_fonts = [
  {id: 1, code: 'base', title: 'Shinonome Gothic (+Baekmuk +some Chinese)', fonts: [:gothic, :extras_fullwidth, :chinese, :korean]},
  {id: 2, code: 'base-hw', title: 'Shinonome Gothic / halfwidth', fonts: [:latin, :latin_ext_a, :extras]},
  {id: 3, code: 'jp-mincho', title: 'Mincho', fonts: [:mincho]},
  {id: 4, code: 'tw', title: 'Chinese (Taiwan)', fonts: []}, 
  {id: 5, code: 'hk', title: 'Chinese (Hong Kong)', fonts: []}, 
  {id: 6, code: 'cn', title: 'Chinese (Mainland)', fonts: []}, 
]

font_data = {}

fonts.each do |f|
  print "Loading #{f[:title]}..."
  font_data[f[:id]] = read_file(File.new(f[:file], 'r'), f[:codepage], f[:halfwidth])
  print "done\n"
end

file = File.new("add_fonts.sql", "w")
output_fonts.each do |f|
  id = f[:id]
  code = f[:code]
  title = f[:title]
  fonts = f[:fonts]

  print "Generating #{title}...\n"
  characters = {}
  fonts.each { |font_name| characters = characters.merge(font_data[font_name]) }
  write_sql(file, id, code, title, characters)

  print "done\n"
end
