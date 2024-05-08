
<?php
/*unused for now but should work with .js, taken from color_selction*/
$pdo = new PDO('sqlite:colorDatabase.sqlite');
$stmt = $pdo->query('SELECT * FROM colors');
$colors = $stmt->fetchAll(PDO::FETCH_ASSOC);
