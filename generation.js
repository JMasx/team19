var selected_color = "";

var selected_cells = []; // list of color selected cells
var position = 0;

const colors = [
    { name: 'Red', hex: '#FF0000' },
    { name: 'Orange', hex: '#FFA500' },
    { name: 'Yellow', hex: '#FFFF00' },
    { name: 'Green', hex: '#008000' },
    { name: 'Blue', hex: '#0000FF' },
    { name: 'Purple', hex: '#800080' },
    { name: 'Teal', hex: '#008080' },
    { name: 'Brown', hex: '#A52A2A' },
    { name: 'Grey', hex: '#808080' },
    { name: 'Black', hex: '#000000' }
];

function generateTables() {
    const nonNumericInputMessage = "Error: Input must be a numeric type";
    const firstInputError = "Error: Input one must be in range of 1-10";
    const secondInputError = "Error: Input two must be in range of 1-26"; 
    const inputMismatchError = "Error: Must enter two numbers";
    const firstInputErrorID = "input1-error";
    const secondInputErrorID = "input2-error";
    let error = false;

    let colorInput = document.getElementById("color-input").value.trim();
    let colorInputArray = colorInput.split(" ");

    generateErrorMessage("", firstInputErrorID);
    generateErrorMessage("", secondInputErrorID);

    if(colorInputArray.length < 2) {
        generateErrorMessage(inputMismatchError, firstInputErrorID);
        return;
    }

    let inputOne = parseInt(colorInputArray[0]);
    let inputTwo = parseInt(colorInputArray[1]);

    if(!Number.isInteger(inputOne) || !Number.isInteger(inputTwo)) {
        generateErrorMessage(nonNumericInputMessage, firstInputErrorID);
        error = true;
    } 

    if(inputOne < 1 || inputOne > 10) {
        generateErrorMessage(firstInputError, firstInputErrorID);
        error = true;
    } 

    if (inputTwo < 1 || inputTwo > 26) {
        generateErrorMessage(secondInputError, secondInputErrorID);
        error = true;
    }

    if(!error) {
        generateTopTable(inputOne);
        generateBottomTable(inputTwo);
    }
}


function generateErrorMessage(message, containerID) {
    var container = document.getElementById(containerID);
    container.textContent = message;
    container.style.color = "red";
}


function generateTopTable(colorInput) {
    let tableContainer = document.getElementById("top-table-container");
    let table = document.createElement("table");
    table.className = "upper-table";
    table.style.width = '100%';
    table.style.borderCollapse = 'collapse';

    for (let rows = 0; rows < colorInput; rows++) {
        let row = table.insertRow();
        row.className = "upper-table-row";

        let inputCell = row.insertCell();
        inputCell.style.width = "30%"; 
        inputCell.style.border = '2px solid';
        
        inputCell.innerHTML = generateDropDown();
        //add the radio button
        inputCell.innerHTML = inputCell.innerHTML + `<input type="radio" name="selection${0}" style="margin-left: 10px;" />`

        // Cell for additional content or future use
        let contentCell = row.insertCell();
        contentCell.style.width = "70%";
        contentCell.style.border = '2px solid';
        contentCell.innerHTML = " "; // names of color tiles will be put here eventually
    }
    tableContainer.innerHTML = ""; 
    tableContainer.appendChild(table);

    listColorOnebyOne();
    preventTwoOfSame();

    //listeners for buttons
    attatchButtons();
}


function generateDropDown(){
    let dropDown = '<select>';
    colors.forEach(color => {
        dropDown += `<option value="${color.name}">${color.name} (${color.hex})</option>`;
    });
    dropDown += '</select>';
    return dropDown;
}

//Created function to list drop down colors different by row.
function listColorOnebyOne(){
    const colors = ['Red', 'Orange', 'Yellow', 'Green', 'Blue', 'Purple', 'Teal', 'Brown', 'Grey', 'Black', ];
    const dropdowns = document.querySelectorAll('.upper-table select');
    let colorMenu = 0;
    for (let i = 0; i < dropdowns.length; i++) {
        dropdowns[i].value = colors[colorMenu];
        colorMenu = (colorMenu + 1) % colors.length
    }
}

function attatchButtons() {
    const radios = document.querySelectorAll('input[type="radio"][name="selection0"]');
    radios.forEach(function(radio) {
        radio.addEventListener('change', function(event) {
            
            if (event.target.checked) {
                changeCellColors(event.target.previousElementSibling.value);
            }
        });
    });
}

function changeCellColors(color) {
    const old_color = selected_color;
    selected_color = color;
    console.log("NEW SELECTEED COLOR " + selected_color)
    const cells = document.querySelectorAll('#bottom-table-container .lower-table td');
        cells.forEach(cell => {
            if (cell.style.backgroundColor === old_color.toLowerCase() && old_color.length > 0) {
                cell.style.backgroundColor = selected_color;
                let old_position = position;
                let cell_position = findCheckedButton();
                //delete old selected
                writeToContentCell(old_position, "");
                writeToContentCell(cell_position, selected_cells.toString());
            }
        });
}


//Created function revert if same color is picked.
function preventTwoOfSame() {
    const dropdowns = document.querySelectorAll('.upper-table select');
    for (let i = 0; i < dropdowns.length; i++) {
        dropdowns[i].addEventListener('change', function(event) {
            duplicatesInDropDown(event);
        });
    }
}

//Created subroutine to throw non-invasive error if same color is picked.
function duplicatesInDropDown(event) {
    const errorMessage = "Error: Cannot select the same colors simultaneously. If the color name doesn't revert immediately, change it back manually, and the error should be resolved after a few selections.";
    const dropdowns = document.querySelectorAll('.upper-table select');
    const duplicateColor = event.target.value;
    for (let i = 0; i < dropdowns.length; i++) {
        if (dropdowns[i] !== event.target && dropdowns[i].value === duplicateColor) {
            const existingColor = event.target.dataset.prevValue;
            event.target.value = existingColor;
            generateErrorMessage(errorMessage, "upper-table-error");
            return;
        }
    }
    console.log(event.target.value);
    if(event.target.nextElementSibling.checked) {
        changeCellColors(duplicateColor);
    }
    generateErrorMessage(" ", "upper-table-error");
    event.target.dataset.prevValue = duplicateColor;
}


//color getter
function getColor(colorList) {
    for (let color of colorList) {
        if (!colorList.has(color)) {
            return color;
        }
    }
}

//Tried to make this perfeclty square but still need to make this square but I figured no point trying to do that now since we will be doing design later.
function generateBottomTable(dimensions) {
    const tableContainer = document.getElementById("bottom-table-container");
    const table = document.createElement("table");
    table.className = "lower-table";
    const size = dimensions + 1;

    const cellSize = '30px'; // doubled the size for the graph.

    for (let rows = 0; rows < size; rows++) {
        const row = table.insertRow();

        for (let columns = 0; columns < size; columns++) {
            const cell = row.insertCell();
            
            cell.addEventListener('click', function() {
                clickedCells(rows, columns);
            });

    
            table.style.borderCollapse = 'collapse';
            cell.style.border = '2px solid';
            cell.style.width = cellSize;
            cell.style.height = cellSize;
            
            if (rows === 0 && columns > 0) {
                const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                const letter = alphabet[columns - 1];
                cell.innerText = letter;
            } else if (columns === 0 && rows > 0) {
                cell.innerText = rows.toString();
            } else if (rows === 0 && columns === 0) {
                cell.innerHTML = " ";
            } else {
                const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                const letter = alphabet[columns - 1];
                const cellID = `${letter}-${rows}`;
                cell.id = cellID;
                console.log(cell.id); 
            }
        }
    }

    // Clear the table container and append the generated table
    tableContainer.innerHTML = "";
    tableContainer.appendChild(table);
}


function clickedCells(row, column) {
    const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const letter = alphabet[column - 1];
    const cell_id = letter + "-" + row;
    if(selected_color === "") {
        alert("Please use the radio buttons to select a color before selecting a grid space.");
    } else {
        const selected_cell = document.getElementById(cell_id);
       if(selected_cell) {
        if(selected_cell.title !== "clicked") {
            selected_cell.title = "clicked";
            selected_cell.style.backgroundColor = selected_color;
            selected_cells.push(letter + row);
            selected_cells.sort();
            let contentCellPosition = findCheckedButton();
            writeToContentCell(contentCellPosition, selected_cells.toString());
        } else {
            selected_cell.title = "";
            selected_cell.style.backgroundColor = "";
            let index = selected_cells.indexOf(letter + row);
            selected_cells.splice(index,1);
            let contentCellPosition = findCheckedButton();
            writeToContentCell(contentCellPosition, selected_cells.toString());
        }
       
       } else {
            console.error("CELL NOT FOUND");
       }
    }
}

function findCheckedButton() {
    // loop the radio buttons and find the one that is checked
    position = 0;
    const radios = document.querySelectorAll('input[type="radio"][name="selection0"]');
    for(let i = 0; i < radios.length; ++i) {
        let radio = radios[i];
        if(radio.checked) {
            break;
         }
         ++position;
    }
    return position;
}

function writeToContentCell(position, textContent) {
    let tableContainer = document.getElementById("top-table-container");
    let table = tableContainer.querySelector("table");
    if(table) {
        console.log("HI");
        let rows = table.rows;
        let contentCell = rows[position].cells[1];
        contentCell.textContent = textContent;
    }
}


 
function printingGraphs() {
    //Looped through the drop downs selected then converted the choice into plain text
    const graphs = ['top-table-container', 'bottom-table-container'];
    for (let i = 0; i < graphs.length; i++) {
        const graphNames = graphs[i]; 
        const whichTable = document.getElementById(graphNames);
        const thisColor = whichTable.querySelectorAll('select');
        for (let j = 0; j < thisColor.length; j++) {
            const select = thisColor[j];
            const keptDropDownColor = select.options[select.selectedIndex].text;
            const colorPlainText = document.createElement('span'); 
            colorPlainText.textContent = keptDropDownColor; 
            select.parentElement.replaceChild(colorPlainText, select); 
        }
    }
    //Save the data for printing on new html
    const upperTableHtml = document.getElementById('top-table-container').innerHTML;
    const lowerTableHtml = document.getElementById('bottom-table-container').innerHTML;

    sessionStorage.setItem('upperTable', upperTableHtml);
    sessionStorage.setItem('lowerTable', lowerTableHtml);

    window.location.href = 'printing.html';
}
