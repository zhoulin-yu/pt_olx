import re
import json

f = open("list_category_id.txt")
str = f.read()
list_str = str.split('<li>')

line = list_str[1]

pattern_id = r'data-value="([0-9]{3,4})"'
cat_id_list = re.findall(pattern_id,str)
print(len(cat_id_list))


pattern_band = r"(?<=#>)[a-zA-Z]{4,10}" 
pattern_band_2 = r">(.+?)<span"
cat_name_list = re.findall(pattern_band_2,str)
print(len(cat_name_list))
#>Hyundai<span
dict = {}
for i in range(len(cat_id_list)):
    dict[cat_id_list[i]] = cat_name_list[i]

with open('list_cat.json',"w") as fj:
    json.dump(dict,fj)

