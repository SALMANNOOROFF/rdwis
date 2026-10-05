from PIL import Image

for name in ["row3", "row4", "row6"]:
    im = Image.open(f"scratch/{name}.png").convert("L")
    extrema = im.getextrema()
    colors = im.getcolors(maxcolors=256*256)
    dark_count = sum(count for count, val in colors if val < 128)
    print(f"{name}: extrema={extrema}, dark pixels={dark_count}")
