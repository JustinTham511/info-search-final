import csv

path = 'test.csv'
    
with open(path, 'r', newline='') as file:
    reader = csv.reader(file)
    existing_data = list(reader)

new_data = ['new_value_1', 'new_value_2', 'new_value_3']
existing_data.append(new_data)

print(existing_data)

with open(path, 'w', newline='') as file:
    writer = csv.writer(file)
    writer.writerows(existing_data)
