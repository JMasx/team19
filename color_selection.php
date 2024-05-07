<!--
User: JM
Edits: 121
Executions: 110
-->

<?php
$pdo = new PDO('sqlite:colorDatabase.sqlite');
$colorCheck =0;
//In your database you will add a table called "colors", each row represents a unique color. 
//The colors have an "id", "Name", and hex value. All 3 of these are Unique and cannot be "Null"
$pdo->exec("CREATE TABLE IF NOT EXISTS colors (
                id INTEGER PRIMARY KEY,
                name TEXT UNIQUE NOT NULL,
                hexValue TEXT UNIQUE NOT NULL)");



$stmt = $pdo->query("SELECT COUNT(*) FROM colors");
if ($stmt->fetchColumn() == 0) {
    //colors from generation.js 6-17
    $colors = [
        ['name' => 'Red', 'hexValue' => '#FF0000'],
        ['name' => 'Orange', 'hexValue' => '#FFA500'],
        ['name' => 'Yellow', 'hexValue' => '#FFFF00'],
        ['name' => 'Green', 'hexValue' => '#008000'],
        ['name' => 'Blue', 'hexValue' => '#0000FF'],
        ['name' => 'Purple', 'hexValue' => '#800080'],
        ['name' => 'Teal', 'hexValue' => '#008080'],
        ['name' => 'Brown', 'hexValue' => '#A52A2A'],
        ['name' => 'Grey', 'hexValue' => '#808080'],
        ['name' => 'Black', 'hexValue' => '#000000']
    ];

    //add to database 
    foreach ($colors as $newColor) {
        $stmt = $pdo->prepare("INSERT INTO colors (name, hex_value) VALUES (:name, :hex_value)");
        $stmt->execute([':name' => $newColor['name'], ':hexValue' => $newColor['hexValue']]);
    }
}

/*The first interface is to Add a new color, ideally the user should only have to enter a name and hex value for the color. 
*Using GET & POST form reqs, we handle user input on click of button
Resource used: https://www.sitepoint.com/community/t/if--server-request-method-post-vs-isset-submit/252336/3 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_color'])) {//add form check
    $name = $_POST['name']; $hexValue = $_POST['hexValue'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM colors WHERE name = :name OR hex_value = :hex_value");
    $stmt->execute([':name' => $name, ':hex_value' => $hexValue]);
    $colorCheck = $stmt->fetchColumn();

    if ($colorCheck > 0) {//if more than 0 columns match the userinput then error
    } 
    
    else {
        $stmt = $pdo->prepare("INSERT INTO colors (name, hex_value) VALUES (:name, :hex_value)");
        $stmt->execute([':name' => $name, ':hex_value' => $hexValue]);
    }
}

/*The second interface is to allow the user to Edit an existing color. This should allow them to change the name and/or the hex value of the color. 
*Almost the same as above but we use UPDATE  with index (id) instead of INSERT INTO tables to modify values rather than create new fields.*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_color'])) {//edit form check
    $id = $_POST['id']; $name = $_POST['name']; $hexValue = $_POST['hexValue'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM colors WHERE (name = :name OR hexValue = :hex_value) AND id != :id");
    $stmt->execute([':name' => $name, ':hex_value' => $hexValue, ':id' => $id]);
    $colorCheck = $stmt->fetchColumn();

    if ($colorCheck > 0) {//if more than 0 columns match the userinput then error
        //echo "This color would conflict with an already existing name or hex value.";//needs fixing
    } 
  
    else {
        $stmt = $pdo->prepare("UPDATE colors SET name = :name, hexValue = :hex_value WHERE id = :id");
        $stmt->execute([':id' => $id, ':name' => $name, ':hex_value' => $hex_value]);
    }
}

/*Lastly there should be an interface for deleting a color.
* Easiest one, simply remove based on id using the SQL DELETE command*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_color'])) {//del form cehck
    $id = $_POST['id'];
    $stmt = $pdo->prepare("DELETE FROM colors WHERE id = :id");
    $stmt->execute([':id' => $id]);
}




$stmt = $pdo->query('SELECT * FROM colors');//statement selecting all colors in colors table
$colors = $stmt->fetchAll(PDO::FETCH_ASSOC);//All data in stmt received from sql table is stored in $colors to show current colors.

// Reset colors to default
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_color'])) {
    static $defaultColors = [
        ['name' => 'Red', 'hexValue' => '#FF0000'],
        ['name' => 'Orange', 'hexValue' => '#FFA500'],
        ['name' => 'Yellow', 'hexValue' => '#FFFF00'],
        ['name' => 'Green', 'hexValue' => '#008000'],
        ['name' => 'Blue', 'hexValue' => '#0000FF'],
        ['name' => 'Purple', 'hexValue' => '#800080'],
        ['name' => 'Teal', 'hexValue' => '#008080'],
        ['name' => 'Brown', 'hexValue' => '#A52A2A'],
        ['name' => 'Grey', 'hexValue' => '#808080'],
        ['name' => 'Black', 'hexValue' => '#000000']
    ];
    //clear current db
    $pdo->exec("DELETE FROM colors");

    //default colors INSERT INTO  the database
    foreach ($defaultColors as $newColor) {
        $stmt = $pdo->prepare("INSERT INTO colors (name, hex_value) VALUES (:name, :hex_value)");
        $stmt->execute([':name' => $newColor['name'], ':hex_value' => $newColor['hexValue']]);
    }
}
$pdo = null;//end php doc obj, close db tether
?>
<!--------------------------------------------------------End of PHP---------------------------------------------------->





<!DOCTYPE html>
<html>
    <head>
        <title>T.A.J.J Inc. Color Generation</title>
        <link rel="stylesheet" href="styles/color_generation.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="selection.js"></script>
    </head>
    <body>


    <nav class="navigation">
            <img src="assets/TAJJLogo.jpg" alt="Photo of Placeholder Logo" >
                <a href="index.php">Home</a>
                <a href="about_us.html">About Us</a>
                <a href="color_generation.html">Color Generation</a>
                <a href="color_selection.php">Color Selection</a>
        </nav>
        <div class="content">
            <h1>T.A.J.J. Inc. Color Selection Product</h1>
        </div>


    <?php  if ($colorCheck > 0): ?>
        <p style="color: red; text-align: center;"><?php echo "This color would conflict with an already existing name or hex value."; ?></p>      <!--RED ERROR EDIT HERE, can use js to make a dissapearing error message if you want-->
    <?php endif; ?>


    <h2>Add Colors</h2>
    <form method="post">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" placeholder="A name for your color"required>
        <label for="hexValue">Hex Value:</label>
        <input type="text" id="hexValue" name="hexValue" pattern="#[a-fA-F0-9]+$" placeholder="#XXXXXX" required>
        <button type="submit" name="add_color">Add Color</button>
    </form>

    <h2>Edit Colors</h2>
    <form method="post">
        <label for="edit_color">Select Color:</label>
        <select id="edit_color" name="id">
            <?php foreach ($colors as $newColor): ?>
                <option value="<?= $newColor['id'] ?>"><?= $newColor['name'] ?></option>
            <?php endforeach; ?>
        </select>
        <label for="edit_name">New Name:</label>
        <input type="text" id="edit_name" name="name" placeholder="A name for your color"required>
        <label for="edit">New Hex Value:</label>
        <input type="text" id="edit" name="hexValue" pattern="#[a-fA-F0-9]+$" placeholder="#XXXXXX" required>
        <button type="submit" name="edit_color">Edit Color</button>
    </form>

    <h2>Delete Color</h2>
    <?php if (count($colors) > 1): ?>
        <form method="post">
            <label for="delete_color">Select Color:</label>
            <select id="delete_color" name="id">
                <?php foreach ($colors as $newColor): ?>
                    <option value="<?= $newColor['id'] ?>"><?= $newColor['name'] ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="delete_color">Delete Color</button>
        </form>
    <?php else: ?>
        <p>You cannot delete the last color.</p>
    <?php endif; ?>

    <h2>Reset Colors</h2>
    <form method="post">
        <button type="submit" name="reset_color">Click twice to confirm Reset Colors</button>
    </form>

    <h2>Current Colors</h2>
    <ul>
        <?php if (isset($colors)): ?>
            <?php foreach ($colors as $newColor): ?>
                <li><?= $newColor['name'] ?> - <?= $newColor['hex_value'] ?></li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</body>
<footer>Created by Team 19</footer>
</html>
