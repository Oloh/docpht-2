<?php

/**
 * This file is part of the DocPHT project.
 * * @author Valentino Pesce
 * @copyright (c) Valentino Pesce <valentino@iltuobrand.it>
 * @copyright (c) Craig Crosby <creecros@gmail.com>
 * * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Forms;

use App\Core\Translations\T;
use Nette\Forms\Form; // This line is added

class VersionSelectForm extends MakeupForm
{
    // This now correctly matches the parent MakeupForm::create() method
    public function create(): Form 
    {
        if (isset($_SESSION['Active']) && isset($_SESSION['page_id'])) {
            $id = $_SESSION['page_id'];
            
            $form = new Form;
            $form->onRender[] = [$this, 'bootstrap4'];
            
            $form->addSelect('version_select', T::trans('Select a version'), $this->versionModel->getSameId($id))
                ->setHtmlAttribute('onChange', 'this.form.submit()')
                ->setDefaultValue($this->pageModel->getVersion($id));

            $form->addSubmit('submit', T::trans('Switch'));

            if ($form->isSuccess()) {
                $values = $form->getValues();
                $this->pageModel->setVersion($id, $values->version_select);
                if (isset($this->flasher)) {
                    $this->flasher->addSuccess(T::trans('Data successfully updated.'));
                }
                header('Location:' . $_SERVER['HTTP_REFERER']);
                exit;
            }

            return $form;
            
        } else {
            // Return an empty form if the session is not set
            return new Form;
        }
    }
}