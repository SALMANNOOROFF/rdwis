from PIL import Image

img = Image.open("scratch/page_34_annex_k.png")
# Let's save a crop of the left column (x from 150 to 700, y from 220 to 1200)
crop1 = img.crop((150, 220, 700, 1200))
crop1.save("scratch/annex_k_left_column.png")
print("Saved scratch/annex_k_left_column.png")
