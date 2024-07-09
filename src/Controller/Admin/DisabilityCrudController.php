<?php

namespace App\Controller\Admin;

use App\Entity\Disability;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class DisabilityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Disability::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name'),
            TextField::new('icon'),
            TextField::new('slug')->hideOnForm(),
        ];
    }
}
