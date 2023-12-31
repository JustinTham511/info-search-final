
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Encode+Sans:wght@200;400;700;900&family=Open+Sans&family=Raleway:wght@400;600;700;800&display=swap"
        rel="stylesheet">
    <title>AP News Search</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: 'Encode Sans', sans-serif;
            font-family: 'Open Sans', sans-serif;
            font-family: 'Raleway', sans-serif;
        }

        body {
            background-color: #ECECEC;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: black;
            padding: 0.75em 1em;
        }

        h1 {
            font-weight: 800;
            color: white;
        }

        .search-container {
            width: 60%;
            display: flex;
            justify-content: flex-end;
        }

        .username-box {
            width: 20%;
            padding: 8px;
            font-size: 16px;
            margin-right: 4px;
        }

        .search-box {
            width: 60%;
            padding: 8px;
            font-size: 16px;
            margin-right: 4px;
        }

        .search-button {
            padding: 8px 16px;
            font-size: 16px;
            background-color: #FF312F;
            color: white;
            border: none;
            cursor: pointer;
        }

        .search-results {
            margin-top: 1em;
            margin-left: 1em;
            margin-right: 1em;
            font-size: 18px;
            font-weight: 600;
        }

        h2 {
            color: black;
        }

        .individual-result hr {
            height: 0.5px;
            background-color: #999;
            border: none;
        }

        .individual-result a {
            color: black;
            display: block;
            margin-top: 10px;
            margin-bottom: 10px;
            text-decoration: none;
        }

        .individual-result p {
            font-size: 14px;
            font-weight: 400;
            margin-bottom: 1em;
            color: #5c5c5c;
        }

        .individual-result a:hover {
            color: #999999;
        }
    </style>

</head>

<body>
    <header>
        <h1>AP News Crawl</h1>
        <form action="search.php" class="search-container" method="post">
			<!-- <input type="text" class="username-box" name="username" placeholder="Username" value="<?php echo $_POST["username"];?>" required /> -->
			<input type="text" class="search-box" name="search_string" placeholder="Search AP News!" value="<?php echo $_POST["search_string"];?>" required />
			<input type="submit" class="search-button" value="Search"/>
		</form>
</header>

<div class="search-results">
	<?php
        // error_reporting(E_ALL);
        // ini_set('display_errors', '1');
        exec("sort -r logs.txt | uniq -c > top_queries.txt");

            $log_file = fopen("top_queries.txt", "r");

            if($log_file) {
                $lineCount = 0;
                $topqueries = '<ul>';
                while (($line = fgets($log_file)) !== false && $lineCount < 5) {
                    $query = substr($line, 8);
                    $allLines .= "<li>$query</li>";
                    // $allLines .= "<p>$line<p>";
                    $lineCount++;
                }
                echo "<h2>Trending Queries</h2>";
                $allLines .= "</ul>";
                echo $allLines;
            }
    
		if (isset($_POST["search_string"])) {
			$search_string = $_POST["search_string"];
            $path = 'test.csv';

			// $ufile = fopen("userlog.py", "w");
			
            // fwrite($ufile, "import pandas as pd\n");
            // fwrite($ufile, "path = './test.csv'\n");
            // fwrite($ufile, "df = pd.read_csv(path)\n");
            // fwrite($ufile, "query = \"$search_string\"\n");
            // fwrite($ufile, "if query not in df['query'].values:\n");
            // fwrite($ufile, "\tdf.loc[len(df)] = {'query': query, 'docid': 1, 'count': 0}\n");
            // fwrite($ufile, "else:\n");
            // fwrite($ufile, "\tdf.loc[df['query'] == query, 'count'] += 1\n");
            // fwrite($ufile, "df.to_csv(path, index=False)");

            // fclose($ufile);

            // $csvData = array_map('str_getcsv', file($path));
            // $header = array_shift($csvData);
            // $csvArray = array_map(function($row) use ($header) {
            //     return array_combine($header, $row);
            // }, $csvData);
            // var_dump($csvArray);

            // $found = false;
            // foreach ($csvArray as &$row) {
            //     if ($row['query'] === $search_string) {
            //         $row['count']++;
            //         $found = true;
            //         break;
            //     }
            // }

            // if (!$found) {
            //     $csvArray[] = array('query' => $search_string, 'docid' => 1, 'count' => 0);
            // }

            // $file = fopen($path, 'w');
            // fputcsv($file, $header);
            // foreach ($csvArray as $row) {
            //     fputcsv($file, $row);
            // }
            // fclose($file);
            file_put_contents("logs.txt", $search_string.PHP_EOL, FILE_APPEND | LOCK_EX);

            fclose($log_file);

            $qfile = fopen("query.py", "w");

			fwrite($qfile, "import pyterrier as pt\nif not pt.started():\n\tpt.init()\n\n");
			fwrite($qfile, "import pandas as pd\nqueries = pd.DataFrame([[\"q1\", \"$search_string\"]], columns=[\"qid\",\"query\"])\n");
			fwrite($qfile, "index = pt.IndexFactory.of(\"./ap-index/\")\n");
			fwrite($qfile, "tf_idf = pt.BatchRetrieve(index, wmodel=\"TF_IDF\")\n");
			fwrite($qfile, "results = tf_idf.transform(queries)\n");

            for ($i=0; $i<10; $i++) {
                fwrite($qfile, "\nprint(index.getMetaIndex().getItem(\"filename\",results.docid[$i]))\n");
                fwrite($qfile, "if index.getMetaIndex().getItem(\"title\", results.docid[$i]).strip() != \"\":\n");
		        fwrite($qfile, "\tprint(index.getMetaIndex().getItem(\"title\",results.docid[$i]))\n");
                fwrite($qfile, "else:\n\tprint(index.getMetaIndex().getItem(\"filename\",results.docid[$i]))\n");
                fwrite($qfile, "print(index.getMetaIndex().getItem(\"text\",results.docid[$i]))\n");
            }

            fclose($qfile);

   			exec("ls | nc -u 127.0.0.1 10017");
   			sleep(3);

            $stream = fopen("output", "r");

            $lines = file($stream);
            // Remove the first line
            array_shift($lines);
            // Write the modified array back to the file
            file_put_contents($stream, implode('', $lines));

            $line=fgets($stream);

            $docs = [];
            $current_doc = [];
            
            while (($line = fgets($stream)) !== false) {
                if (strpos($line, "/content/drive/MyDrive/SearchRec Group Project/data/") !== false) {
                    if (!empty($current_doc)) {
                        $docs[] = $current_doc;
                        $current_doc = [];
                    }
                    $current_doc['url'] = str_replace("/content/drive/MyDrive/SearchRec Group Project/data/", "", $line);
                } elseif (strpos($line, "Copyright 2023 The Associated Press. All Rights Reserved.") !== false) {
                    $blurb = str_replace("Copyright 2023 The Associated Press. All Rights Reserved.  ", "", $line);
                    $blurb = strstr($blurb, "  ", true); // Extract everything before "  "
                    $current_doc['blurb'] = $blurb;
                } elseif (!empty($line)) {
                    $current_doc['title'] = $line;
                }
            }
            
            // Adding the last document
            if (!empty($current_doc)) {
                $docs[] = $current_doc;
            }
            
            foreach ($docs as $doc) {
                $url = $doc['url'];
                $title = $doc['title'];
                $blurb = $doc['blurb'];
                
                echo "<div class='individual-result'><a href=\"http://$url\">".$title."</a><p>".$blurb."</p><hr/></div>\n";
            }
            
            fclose($stream);

            exec("rm userlog.py");
            exec("rm query.py");
            exec("rm output");
        }
	?>
</div>

</body>
</html>
