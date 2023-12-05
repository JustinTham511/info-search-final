<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Encode+Sans:wght@200;400;700;900&family=Open+Sans&family=Raleway:wght@400;600;700;800&display=swap"
        rel="stylesheet">
    <title>Sydney's Search</title>
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
			<input type="text" class="username-box" name="username" placeholder="Username" value="<?php echo $_POST["username"];?>" required />
			<input type="text" class="search-box" name="search_string" placeholder="Search AP News!" value="<?php echo $_POST["search_string"];?>" required />
			<input type="submit" class="search-button" value="Search"/>
		</form>
</header>

<div class="search-results">
	<?php
		if (isset($_POST["search_string"])) {
			$search_string = $_POST["search_string"];
			$user_string = $_POST["user_string"];
			$qfile = fopen("query.py", "w");
			$ufile = fopen("$user_string.txt", "w");
			
			fwrite($ufile, $search_string);
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

			fclose($ufile);
            fclose($qfile);

   			exec("ls | nc -u 127.0.0.1 10032");
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

            exec("rm query.py");
            exec("rm output");
        }
	?>
</div>

</body>
</html>
