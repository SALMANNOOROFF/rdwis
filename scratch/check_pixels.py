from PIL import Image
import numpy as np

for name in ["row3", "row4", "row6"]:
    im = Image.open(f"scratch/{name}.png").convert("L")
    arr = np.array(im)
    dark_pixels = (arr < 128).sum()
    print(f"{name}: min={arr.min()}, max={arr.max()}, mean={arr.mean():.1f}, dark pixels={dark_pixels}")
