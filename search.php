<html>
<head>
	<title>Sydney's Search</title>
	<style>
	        body {
        	    	font-family: Arial, sans-serif;
            		margin: 20px;
        	}
        	h1 {
            		text-align: center;
        	}
        	.search-container {
            		text-align: center;
           		 margin-bottom: 20px;
        	}
        	.search-box {
            		width: 60%;
            		padding: 8px;
            		font-size: 16px;
        	}
        	.search-button {
            		padding: 8px 16px;
            		font-size: 16px;
            		background-color: #4CAF50;
            		color: white;
            		border: none;
            		cursor: pointer;
        	}
        	.search-results {
            		margin-left: 20px;
        	}
        	.search-results a {
            		display: block;
            		margin-bottom: 10px;
            		text-decoration: none;
            		color: #0366d6;
        	}
        	.search-results a:hover {
            		text-decoration: underline;
        	}
    </style>
</head>

<body>

<h1>Search AP News!</h1>

<form action="search.php" method="post">
	<input type="text" class="search-box" size=40 name="search_string" placeholder="Search Anything!" value="<?php echo $_POST["search_string"];?>"/>
	<input type="submit" class="search-button" value="Search"/>
</form>

<div class="search-results">
	<?php
		if (isset($_POST["search_string"])) {
			$search_string = $_POST["search_string"];
			$qfile = fopen("query.py", "w");

			fwrite($qfile, "import pyterrier as pt\nif not pt.started():\n\tpt.init()\n\n");
			fwrite($qfile, "import pandas as pd\nqueries = pd.DataFrame([[\"q1\", \"$search_string\"]], columns=[\"qid\",\"query\"])\n");
			fwrite($qfile, "index = pt.IndexFactory.of(\"./ap-index/\")\n");
			fwrite($qfile, "tf_idf = pt.BatchRetrieve(index, wmodel=\"TF_IDF\")\n");
			fwrite($qfile, "results = tf_idf.transform(queries)\n");

			for ($i=0; $i<5; $i++) {
				fwrite($qfile, "print(index.getMetaIndex().getItem(\"filename\",results.docid[$i]))\n");
				fwrite($qfile, "if index.getMetaIndex().getItem(\"title\", results.docid[$i]).strip() != \"\":\n");
				fwrite($qfile, "\tprint(index.getMetaIndex().getItem(\"title\",results.docid[$i]))\n");
				fwrite($qfile, "else:\n\tprint(index.getMetaIndex().getItem(\"filename\",results.docid[$i]))\n");
   			}
   
   			fclose($qfile);

   			exec("ls | nc -u 127.0.0.1 10017");
   			sleep(3);

   			$stream = fopen("output", "r");

   			$line=fgets($stream);

   			while(($line=fgets($stream))!=false) {
   				$clean_line = preg_replace('/\s+/',',',$line);
   				$record = explode("./", $clean_line);
   				$line = fgets($stream);
   				echo "<a href=\"http://$record[1]\">".$line."</a><br/>\n";
   			}

   			fclose($stream);
   
  			exec("rm query.py");
  			exec("rm output");
   			}
	?>
</div>

</body>
</html>
