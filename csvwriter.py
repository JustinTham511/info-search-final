import pandas as pd
path = 'test.csv'
df = pd.read_csv(path)
query = 'washington huskies'
if query not in df['query'].values:
    df.loc[len(df)] = {'query': query, 'docid': 1, 'count': 0}
else:
    df.loc[df['query'] == query, 'count'] += 1
df.to_csv(path, index=False)
