import pandas as pd
import csv
import numpy as np

list_ad_file='pt_100_ad_with_tel.csv'
data = pd.read_csv(list_ad_file)

data['idannonce'].to_json('list_idannonce.json')



