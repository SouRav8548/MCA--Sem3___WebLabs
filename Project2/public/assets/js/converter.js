document.addEventListener("DOMContentLoaded", () => {
  // Tab Logic
  const tabs = document.querySelectorAll(".tabbar button");
  const panels = ["convCurrency", "convWeight", "convLength", "convNumber"];

  tabs.forEach((tab, index) => {
    tab.addEventListener("click", () => {
      tabs.forEach((t) => t.classList.remove("active"));
      tab.classList.add("active");
      panels.forEach(
        (p) => (document.getElementById(p).style.display = "none")
      );
      document.getElementById(panels[index]).style.display = "grid";
    });
  });

  // Helpers
  const getVal = (id) => parseFloat(document.getElementById(id).value);
  const setRes = (id, val) => (document.getElementById(id).innerText = val);

  // Currency (Fixed Rates)
  const rates = { USD: 1, EUR: 0.92, INR: 83.5, GBP: 0.79, JPY: 151.0 };
  document.getElementById("curConvert").onclick = () => {
    const amt = getVal("curAmount");
    const from = document.getElementById("curFrom").value;
    const to = document.getElementById("curTo").value;
    const res = (amt / rates[from]) * rates[to];
    setRes("curResult", `${res.toFixed(2)} ${to}`);
  };

  // Weight
  const wRates = { g: 1, kg: 1000, lb: 453.592, oz: 28.3495 };
  document.getElementById("wConvert").onclick = () => {
    const val = getVal("wVal");
    const from = document.getElementById("wFrom").value;
    const to = document.getElementById("wTo").value;
    const res = (val * wRates[from]) / wRates[to];
    setRes("wResult", res.toFixed(4));
  };

  // Length
  const lRates = { m: 1, cm: 0.01, km: 1000, ft: 0.3048, in: 0.0254 };
  document.getElementById("lConvert").onclick = () => {
    const val = getVal("lVal");
    const from = document.getElementById("lFrom").value;
    const to = document.getElementById("lTo").value;
    const res = (val * lRates[from]) / lRates[to];
    setRes("lResult", res.toFixed(4));
  };

  // Number System
  document.getElementById("nConvert").onclick = () => {
    const val = document.getElementById("nInput").value;
    const from = parseInt(document.getElementById("nFrom").value);
    const to = parseInt(document.getElementById("nTo").value);
    try {
      const decimal = parseInt(val, from);
      if (isNaN(decimal)) throw new Error();
      const res = decimal.toString(to).toUpperCase();
      setRes("nResult", res);
    } catch (e) {
      setRes("nResult", "Invalid");
    }
  };
});
