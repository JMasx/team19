function takeColorInput() {
    let colorInput = document.getElementById("color-input").value.trim();
    let inputArr = colorInput.split(" ");

    let colorName = inputArr[0];
    let colorHex = inputArr[1];
     
    generateErrorMessage("","color-name-error");
    generateErrorMessage("", "color-hex-error");

    if(colorInputIsValid(inputArr, colorName, colorHex)) {
        // proceed with database stuff
        console.log("yay I passed");
    }
}


function colorInputIsValid(inputArr, colorName, colorHex) {
    const inputNumberMismatch = "Error: Must enter the color name followed by the hex value";
    const hexInputMismatch = "Error: Hex code entered does not match expected for color";
    const invalidHex = "Error: Hex code must be 7 characters including '#'";
    const duplicateError = "Error: This color exists in the table";

    if(inputArr.length != 2) {
        generateErrorMessage(inputNumberMismatch, "color-name-error");
        return false;
    }

    if(colorHex.length !== 7) {
        console.log(colorHex);
        console.log(colorHex.length);
        generateErrorMessage(invalidHex, "color-hex-error");
        return false;
    }
    const hexCode = getHexCodeUsingCanvas(colorName);
    if(hexCode.toLowerCase() !== colorHex.toLowerCase()) {
        generateErrorMessage(hexInputMismatch, "color-hex-error");
        return false;
    }

    return true;
}


function generateErrorMessage(message, containerID) {
    var container = document.getElementById(containerID);
    container.textContent = message;
    container.style.color = "red";
}


function getHexCodeUsingCanvas(colorName) {
    const canvas = document.createElement('canvas');
    canvas.width = 1;
    canvas.height = 1;
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = colorName;
    ctx.fillRect(0, 0, 1, 1);
    const pixelData = ctx.getImageData(0, 0, 1, 1).data;
    const hexCode = '#' + ('000000' + (pixelData[0] << 16 | pixelData[1] << 8 | pixelData[2]).toString(16)).slice(-6);
    return hexCode;
}