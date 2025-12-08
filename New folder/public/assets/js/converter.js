// assets/js/converter.js
document.getElementById("convert-weight").addEventListener("click", () => {
  const v = Number(document.getElementById("weight-value").value) || 0;
  const f = document.getElementById("weight-from").value;
  const t = document.getElementById("weight-to").value;
  const res = v * (weightFactors[f] / weightFactors[t]);
  document.getElementById("weight-result").textContent = res;
});

document.getElementById("convert-length").addEventListener("click", () => {
  const v = Number(document.getElementById("length-value").value) || 0;
  const f = document.getElementById("length-from").value;
  const t = document.getElementById("length-to").value;
  const res = v * (lengthFactors[f] / lengthFactors[t]);
  document.getElementById("length-result").textContent = res;
});

document.getElementById("convert-number").addEventListener("click", () => {
  const val = document.getElementById("num-value").value.trim();
  const from = document.getElementById("num-from").value;
  const to = document.getElementById("num-to").value;
  let dec;
  try {
    if (from === "binary") dec = parseInt(val, 2);
    if (from === "decimal") dec = parseInt(val, 10);
    if (from === "hex") dec = parseInt(val, 16);
    if (from === "octal") dec = parseInt(val, 8);
    let out;
    if (to === "binary") out = dec.toString(2);
    if (to === "decimal") out = dec.toString(10);
    if (to === "hex") out = dec.toString(16);
    if (to === "octal") out = dec.toString(8);
    document.getElementById("num-result").textContent = out;
  } catch (e) {
    document.getElementById("num-result").textContent = "Error";
  }
});
