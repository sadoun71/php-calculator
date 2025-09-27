<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>calculator with PHP</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="app">
    <div class="calculator">
      <input id="display" class="display" type="text" value="" placeholder="0" disabled>
      <div id="error" class="error" hidden></div>

      <div class="buttons">
        <button data-action="ac" class="muted">AC</button>
        <button data-action="c" class="muted">C</button>
        <button data-val="(" class="muted">(</button>
        <button data-val=")" class="muted">)</button>

        <button data-val="7">7</button>
        <button data-val="8">8</button>
        <button data-val="9">9</button>
        <button data-val="/" class="op">÷</button>

        <button data-val="4">4</button>
        <button data-val="5">5</button>
        <button data-val="6">6</button>
        <button data-val="*" class="op">×</button>

        <button data-val="1">1</button>
        <button data-val="2">2</button>
        <button data-val="3">3</button>
        <button data-val="-" class="op">−</button>

        <button data-val="0">0</button>
        <button data-val=".">.</button>
        <button id="equals" class="equals">=</button>
        <button data-val="+" class="op">+</button>
      </div>
    </div>
  </div>

  <script>
    const display = document.getElementById('display');
    const errBox = document.getElementById('error');

    const setError = (msg) => {
      if (!msg) { errBox.hidden = true; errBox.textContent = ''; return; }
      errBox.hidden = false; errBox.textContent = msg;
    };

    document.querySelector('.buttons').addEventListener('click', async (e) => {
      const btn = e.target.closest('button');
      if (!btn) return;
      const act = btn.dataset.action;
      const val = btn.dataset.val;

      if (act === 'ac') { display.value = ''; setError(''); return; }
      if (act === 'c')  { display.value = display.value.slice(0, -1); setError(''); return; }
      if (btn.id === 'equals') { return evaluate(); }
      if (val) { display.value += val; setError(''); }
    });

    async function evaluate() {
      const expression = display.value.trim();
      if (!expression) return;
      try {
        const res = await fetch('calculator.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ expression })
        });
        const data = await res.json();
        if (data.ok) { display.value = data.result; setError(''); }
        else { setError(data.error || 'خطأ'); }
      } catch {
        setError('تعذر الاتصال بالخادم');
      }
    }
  </script>
</body>
</html>
