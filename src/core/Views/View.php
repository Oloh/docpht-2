<?php

declare(strict_types=1);

namespace DocPHT\Core\Views;

class View
{
    private array $defaultVars = [];

    /**
     * Add a default variable to be passed to every view.
     */
    public function addDefault(string $key, $value)
    {
        $this->defaultVars[$key] = $value;
    }

    /**
     * Render a view file.
     */
    public function show(string $path, array $vars = [])
    {
        // Merge default variables with specific view variables
        $finalVars = array_merge($this->defaultVars, $vars);

        // This makes the array keys available as variables (e.g., $t, $PageTitle)
        extract($finalVars, EXTR_SKIP);

        // Include the view file
        include 'src/views/' . $path;
    }

    /**
     * Load a full page with head and footer.
     */
    public function load(string $pageTitle, string $path, array $vars = [])
    {
        $vars['PageTitle'] = $pageTitle;
        $this->show('partial/head.php', $vars);
        $this->show($path, $vars);
        $this->show('partial/footer.php');
    }
}