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
        ['name' => 'notRed', 'hexValue' => '#FF0000'],
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
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM colors WHERE (name = :name OR hex_value = :hex_value) AND id != :id");
    $stmt->execute([':name' => $name, ':hex_value' => $hexValue, ':id' => $id]);
    $colorCheck = $stmt->fetchColumn();

    if ($colorCheck > 0) {//if more than 0 columns match the userinput then error
        //echo "This color would conflict with an already existing name or hex value.";//needs fixing
    } 
  
    else {
        $stmt = $pdo->prepare("UPDATE colors SET name = :name, hex_value = :hex_value WHERE id = :id");
        $stmt->execute([':id' => $id, ':name' => $name, ':hex_value' => $hexValue]);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/color_generation.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function(){
            $(".content").hide(); // Hide content initially

            $("#addButton").click(function(){
                $(".content").hide();
                $("#add_color_form").show();
            });

            $("#editButton").click(function(){
                $(".content").hide();
                $("#edit_color_form").show();
            });

            $("#delButton").click(function(){
                $(".content").hide();
                $("#delete_color_form").show();
            });
    });
    
//responsive box shadow (wasted too much time on this for the "professional look")
//for add colors Dropdown
document.addEventListener("DOMContentLoaded", function() {//ensure all #addButtonDOM is loaded before running since boxshadow would be applied after js typically
    //first iteration
    var firstColor = document.getElementById("edit_color").options[document.getElementById("edit_color").selectedIndex];
    var chosenFirstColor = firstColor.getAttribute("editDropdownColor");
    document.documentElement.style.setProperty("--currentColor", chosenFirstColor);
    document.getElementById("edit_color").addEventListener("change", function() {
        var currColor = this.options[this.selectedIndex];
        var selectedColor = currColor.getAttribute("editDropdownColor");
        document.documentElement.style.setProperty("--currentColor", selectedColor);//same idea as colors.css, thanks jack :)
    });
});

//fpr edit colors dropdown
document.addEventListener("DOMContentLoaded", function() {//ensure all #addButtonDOM is loaded before running since boxshadow would be applied after js typically
    //first iteration
    var firstColor = document.getElementById("delete_color").options[document.getElementById("delete_color").selectedIndex];
    var chosenFirstColor = firstColor.getAttribute("deleteDropdownColor");
    document.documentElement.style.setProperty("--currentColor", chosenFirstColor);
    document.getElementById("delete_color").addEventListener("change", function() {
        var currColor = this.options[this.selectedIndex];
        var selectedColor = currColor.getAttribute("deleteDropdownColor");
        document.documentElement.style.setProperty("--currentColor", selectedColor);//same idea as colors.css, thanks jack :)
    });
});
//end of boxshadow




</script>



<!--Custom style choices, you can move this to a seperate CSS file whenever, I didnt because its easier to work with and there was already a .css attached-->
<style>
h2 {
    text-align: center;
}
.content {
    text-align: center;
    margin: 0 auto;
}

.content label {
    display: block;
    margin-top: 10px;
    padding: 10px 10px;
    margin: auto;
        width: auto;
    max-width: 200px;
}

.content input[type="text"],
.content select {
    width: auto;
    padding: 8px;
    margin-top: 6px;
    margin-bottom: 10px;
    border: 0.5px solid ;
    border-radius: 10px;
    margin: auto;
}

.content button[type="submit"] {
    background-color: #333333;
    color: #CCCCCC;
    cursor: pointer;
    border: none;
    border-radius: 10px;
    padding: 10px 15px;
}

.content button[type="submit"]:hover {
    background-color: #CCCCCC;
    color: #333333;
}
.buttons {
    display:flex;
    text-align: center;
    margin-top: 20px;
    justify-content: space-evenly;
}

.buttons button {
    background-color: #333333;
    color: #CCCCCC;
    cursor: pointer;
    border: none;
    border-radius: 10px;
    padding: 10px 15px;
}

.buttons button:hover {
    background-color: #CCCCCC;
    color: #333333;
}

.colorTable {
    margin-top: 100px;
    text-align: center;
}

.colorTable table {
    border-collapse: collapse;
    margin: 0 auto;
    width: 50%;
}

.colorTable th,
.colorTable td {    
    border: 3px solid;
    border-color: #333333;
    padding: 10px 15px;
    height: auto;
}

.colorTable th {
    background-color: #333333;
}

footer {
    background-color: #333333;  
    color:#E5EBFF;
}



/*Minor "professional-looking" idea's detail implementation was helped by https://stackoverflow.com/questions/13014808/is-there-any-way-to-animate-an-ellipsis-with-css-animations*/
.ellipsis {
    font-size: 15px;
}

.ellipsis:after {
    display: inline-block;
    vertical-align: bottom;
    overflow: hidden;
    -webkit-animation: ellipsis steps(4,end) 1900ms infinite;      
    animation: ellipsis steps(4,end) 1900ms infinite;
    content: "\2026";
    width: 2px;
}

@keyframes ellipsis {
  to {
    width: 1.25em;    
  }
}
@-webkit-keyframes ellipsis {
  to {
    width: 1.25em;    
  }
}
/*end of ellipsis animation */


.visualUpdate {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-color: #E5EBFF;  
    border: 1px solid;
    padding: 5px 5px;
    font-size: 16px;
    border-radius: 5px;
    text-align: center;
    margin-right: 2px;
    width: auto; /* Adjust the width as needed */
}

.visualUpdate:focus {
    border-color: #333333;
    outline: none;
    box-shadow: 0 0 10px var(--currentColor);
}


</style>
</head>
<body>

<nav class="navigation">
    <img src="assets/TAJJLogo.jpg" alt="Photo of Placeholder Logo" >
    <a href="index.php">Home</a>
    <a href="about_us.html">About Us</a>
    <a href="color_generation.html">Color Generation</a>
    <a href="color_selection.php">Color Selection</a>
</nav>

    <h1>Color Gen Editor<sub style = "font-size: 12.5px">by T.A.J.J. Inc.</sub></h1>


<div class="buttons">
    <button id="addButton">Add Colors</button>
    <button id="editButton">Edit Colors</button>
    <button id="delButton">Delete a Color</button>
</div>

<div id="add_color_form" class="content" style="display:none;">
    <h2><div class ="ellipsis">Adding Colors</div></h2>
    <form method="post">

        <label for="name">Name:</label>
        <input type="text" id="name" name="name" placeholder="A name for your color" required>

        <label for="hexValue">Hex Value:</label>
        <input type="text" id="hexValue" name="hexValue" pattern="#[a-fA-F0-9]+$" placeholder="#XXXXXX" required>
    <br><br>
        <button type="submit" name="add_color">Add Color</button>
        
    </form>
</div>

<div id="edit_color_form" class="content" style="display:none;">
<h2><div class ="ellipsis">Editing Colors</div></h2>
    <form method="post">
        <label for="edit_color">Select Color:</label>

        <select id="edit_color" name="id" class ="visualUpdate">
            <?php foreach ($colors as $newColor): ?>
                <option value="<?= $newColor['id'] ?>" editDropdownColor="<?= $newColor['hex_value'] ?>"><?= $newColor['name'] ?></option>
            <?php endforeach; ?>
        </select>

        <label for="edit_name">New Name:</label>
        <input type="text" id="edit_name" name="name" placeholder="A name for your color"required>

        <label for="edit_hexValue">New Hex Value:</label>
        <input type="text" id="edit_hexValue" name="hexValue" pattern="#[a-fA-F0-9]+$" placeholder="#XXXXXX" required>
<br><br>
        <button type="submit" name="edit_color">Edit Color</button>
    </form>
</div>

<div id="delete_color_form" class="content" style="display:none;">
<h2><div class ="ellipsis">Deleting Colors</div></h2>
    <?php if (count($colors) > 1): ?>
        <form method="post">
            <label for="delete_color">Select Color:</label>

            <select id="delete_color" name="id" class ="visualUpdate">
                <?php foreach ($colors as $newColor): ?>
                    <option value="<?= $newColor['id'] ?>" deleteDropdownColor="<?= $newColor['hex_value'] ?>"><?= $newColor['name'] ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" name="delete_color">Delete Color</button>
        </form>
    <?php else: ?>
        <p style="background-color: #333333; color: #CCCCCC; border: none; border-radius: 10px; padding: 10px 15px; width: 250px; margin:auto; border-bottom: 3px solid#f2746b;">
        You cannot delete the last color.</p>
    <?php endif; ?>
</div>

<div id="resetForm" class="content" style="display:none;">

</div>
<br><br><br>


<div class="buttons">    <form method="post">
        <button id="resetButton" type="submit" name="reset_color">Click Twice to Reset Colors</button>
    </form> </div>
<div class="colorTable">
    <h2>Current List of Colors</h2>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Hex Value</th>
            </tr>
        </thead>
        <tbody>
            <?php if (isset($colors)): ?>
                <?php foreach ($colors as $newColor): ?>
                    <tr>
                        <td><?= $newColor['name'] ?></td>
                        <td><?= $newColor['hex_value'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>


</body>
<footer>&copy; 2024 T.A.J.J. Inc. All rights reserved.</footer>
</html> 

