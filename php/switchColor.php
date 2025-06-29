<?php

class ColorTheme
{
    private $c_themes;
    private $cur_theme = 'light';

    public function __construct()
    {
        $this->c_themes = array (   
            'light' => array (
                'name'       => 'светлая',
                'text'       => '#000000',
                'background' => '#ffffff',
                'border'     => '#cccccc',
                'button-bg'  => '#f0f0f0',
                'button-text'=> '#333333'

            ),
            'dark' => array (
                'name'       => 'темная',
                'text'       => '#ffffff',
                'background' => '#000000',   
                'border'     => '#333333',
                'button-bg'  => '#333333',
                'button-text'=> '#ffffff'

            ),
            'contrast' => array (
                'name'       => 'контрастная',
                'text'       => '#ffff00',
                'background' => '#000000',   
                'border'     => '#ffff00',
                'button-bg'  => '#ff0000',
                'button-text'=> '#000000'
            )
        );

        if (isset($_COOKIE['our_theme']) && array_key_exists($_COOKIE['our_theme'], $this->c_themes))
        {
            $this->cur_theme = $_COOKIE['our_theme'];
        }
    }

    public function switchTheme($theme)
    {
        if (array_key_exists($theme, $this->c_themes))
        {
            setcookie('our_theme', $theme, time() + (24*3600), "/");
            $this->cur_theme = $theme;
            return true;
        }
        return false;
    }

    public function getCurTheme()
    {
        return $this->c_themes[$this->cur_theme];
    }

    public function getCurThemeKey()
    {
        return $this->cur_theme;
    }

    public function getThemes()
    {
        return $this->c_themes;
    }
}

$colorTheme = new ColorTheme();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['color_theme'])) 
{
    $colorTheme->switchTheme($_POST['color_theme']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();    
}

$currentTheme = $colorTheme->getCurTheme();
$allThemes = $colorTheme->getThemes();

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            color: <?php echo htmlspecialchars($currentTheme['text']); ?>;
            background-color: <?php echo htmlspecialchars($currentTheme['background']); ?>;
        }   
        select, button {
            background-color: <?php echo htmlspecialchars($currentTheme['button-bg']); ?>;
            color: <?php echo htmlspecialchars($currentTheme['button-text']); ?>;
            border: 1px solid <?php echo htmlspecialchars($currentTheme['border']); ?>;
        }
    </style>
</head>
<body>
<h2>Color switcher!</h2>

<form method="post">
    <label for="color_theme">Select theme:</label>

    <select name="color_theme" id="color_theme">
        <?php foreach ($allThemes as $key => $theme): 
            $selected = ($key === $colorTheme->getCurThemeKey()) ? ' selected' : '';
            echo '<option value="' . htmlspecialchars($key) . '"' . $selected . '>' . htmlspecialchars($theme['name']) . '</option>';
        endforeach; ?>
    </select>
    <br>
    <button type="submit">Apply</button>    
</form>    

<p><?php echo '1: '.htmlspecialchars($colorTheme->getCurTheme()); ?></p>
<p><?php echo '2: '.htmlspecialchars($colorTheme->getCurThemeKey()); ?></p>

<p>Current theme: <?php echo htmlspecialchars($currentTheme['name']); ?></p>

</body>
</html>