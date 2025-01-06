<!-- Choose the website language -->
<form method="GET" action="">
    <select id="language-select" name="lang" onchange="this.form.submit()">
        <option value="fr" <?= ($lang === 'fr') ? 'selected' : '' ?>>Français 🇫🇷</option>
        <option value="en" <?= ($lang === 'en') ? 'selected' : '' ?>>English 🇬🇧</option>
        <option value="es" <?= ($lang === 'es') ? 'selected' : '' ?>>Español 🇪🇸</option>
        <option value="de" <?= ($lang === 'de') ? 'selected' : '' ?>>Deutsch 🇩🇪</option>
        <option value="it" <?= ($lang === 'it') ? 'selected' : '' ?>>Italiano 🇮🇹</option>
        <option value="pt" <?= ($lang === 'pt') ? 'selected' : '' ?>>Português 🇵🇹</option>
        <option value="ru" <?= ($lang === 'ru') ? 'selected' : '' ?>>Русский 🇷🇺</option>
        <option value="uk" <?= ($lang === 'uk') ? 'selected' : '' ?>>Українська 🇺🇦</option>
    </select>
</form>
