<?php

/**
 * This file is part of the DocPHT project.
 * * @author Valentino Pesce
 * @copyright (c) Valentino Pesce <valentino@iltuobrand.it>
 * @copyright (c) Craig Crosby <creecros@gmail.com>
 * * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Forms;

use App\Core\Translations\T;
use Nette\Forms\Form;

class SearchForm extends MakeupForm
{
    public function create(): Form
    {
        $form = new Form;
        $this->bootstrap4($form);

        $form->addText('search', T::trans('Search:'))
            ->setHtmlAttribute('placeholder', T::trans('Search...'))
            ->setHtmlAttribute('value', $_POST['search'] ?? '');

        $form->addSubmit('submit', T::trans('Search'));
        
        // The complex search logic that was here has been moved to the SearchModel 
        // and HomeController. This is a much better practice for modern applications.
        // The form's only responsibility is to define the input fields.

        return $form;
    }

    public function filter(object $file): bool
    {
        $exclude = ['doc-pht','pages.json'];
        return ! in_array($file->getFilename(), $exclude);
    }

    public function sanitizing(string $data): string
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = strtolower($data);
        $data = strip_tags($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}