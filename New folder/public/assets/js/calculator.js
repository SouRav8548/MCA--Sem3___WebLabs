// assets/js/calculator.js
const bdisplay = document.getElementById("bdisplay");
const binArea = document.getElementById("binary-buttons");
const binBtns = [
  "A",
  "B",
  "AND",
  "OR",
  "NOT",
  "XOR",
  "XNOR",
  "+",
  "-",
  "*",
  "/",
  "=",
  "C",
];
binBtns.forEach((b) => {
  const btn = document.createElement("button");
  btn.textContent = b;
  btn.addEventListener("click", () => onBin(b));
  binArea.appendChild(btn);
});
let bstate = { a: "0", b: "0", op: "" };
function onBin(cmd) {
  if (cmd === "C") {
    bstate = { a: "0", b: "0", op: "" };
    bdisplay.value = "";
    return;
  }
  if (cmd === "=") {
    try {
      const A = parseInt(bstate.a, 2);
      const B = parseInt(bstate.b, 2);
      let R = 0;
      switch (bstate.op) {
        case "AND":
          R = A & B;
          break;
        case "OR":
          R = A | B;
          break;
        case "XOR":
          R = A ^ B;
          break;
        case "XNOR":
          R = ~(A ^ B) >>> 0;
          break;
        case "+":
          R = A + B;
          break;
        case "-":
          R = A - B;
          break;
        case "*":
          R = A * B;
          break;
        case "/":
          R = B === 0 ? "Err" : Math.floor(A / B);
          break;
        case "NOT":
          R = ~A >>> 0;
          break;
        default:
          R = "Err";
      }
      if (R === "Err") bdisplay.value = "Error";
      else bdisplay.value = typeof R === "number" ? R.toString(2) : String(R);
    } catch (e) {
      bdisplay.value = "Error";
    }
    return;
  }
  if (["AND", "OR", "XOR", "XNOR", "+", "-", "*", "/", "NOT"].includes(cmd)) {
    bstate.op = cmd;
    return;
  }
  // assume A/B bits entry
  if (!bstate.op) {
    bstate.a = bstate.a === "0" ? cmd : bstate.a + cmd;
    bdisplay.value = bstate.a;
  } else {
    bstate.b = bstate.b === "0" ? cmd : bstate.b + cmd;
    bdisplay.value = bstate.b;
  }
}
