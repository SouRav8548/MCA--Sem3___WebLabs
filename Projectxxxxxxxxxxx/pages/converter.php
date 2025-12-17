async function convertCurrency() {
const from = document.getElementById('from').value;
const to = document.getElementById('to').value;
const amount = document.getElementById('amount').value;

const response = await fetch(`https://api.exchangerate-api.com/v4/latest/${from}?apiKey=YOUR_FREE_KEY`);
const data = await response.json();
const result = amount * data.rates[to];
document.getElementById('result').value = result.toFixed(2);
}