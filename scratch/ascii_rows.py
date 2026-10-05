from PIL import Image

def ascii_art(img_path):
    im = Image.open(img_path).convert("L")
    im = im.resize((70, 20))
    chars = " .:-=+*#%@"
    for y in range(im.height):
        line = ""
        for x in range(im.width):
            val = im.getpixel((x, y))
            line += chars[int((255 - val) / 255 * (len(chars) - 1))]
        if line.strip():
            print(line)

print("--- ROW 3 ---")
ascii_art("scratch/row3.png")
print("\n--- ROW 4 ---")
ascii_art("scratch/row4.png")
print("\n--- ROW 6 ---")
ascii_art("scratch/row6.png")
