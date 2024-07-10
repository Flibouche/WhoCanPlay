<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Image::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('disability'),
            AssociationField::new('feature'),
            AssociationField::new('user'),
            TextField::new('url'),
            TextField::new('title'),
            TextField::new('altText'),
            TextEditorField::new('description'),
            DateTimeField::new('submissionDate')->hideOnForm(),
            DateTimeField::new('updatedAt')->hideOnForm(),
            TextField::new('slug')->hideOnForm(),
        ];
    }
}
