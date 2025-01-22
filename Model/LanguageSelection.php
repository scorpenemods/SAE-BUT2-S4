

<!-- Choose the website language -->
<form method="GET" action="">
    <select id="language-select" name="lang" onchange="this.form.submit()">
        <option value="fr" <?= ($lang === 'fr') ? 'selected' : '' ?>>Français 🇫🇷</option>
        <option value="en" <?= ($lang === 'en') ? 'selected' : '' ?>>English 🇬🇧</option>
        <option value="uk" <?= ($lang === 'uk') ? 'selected' : '' ?>>Українська 🇺🇦</option>
    </select>
</form>
