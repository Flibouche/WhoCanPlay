<?php

namespace App\Controller\Admin;

use App\Entity\Feature;
use App\Enum\FeatureState;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class FeatureCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Feature::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('disability'),
            AssociationField::new('game')
            ->setFormTypeOptions([
                'choice_label' => function ($game) {
                    return sprintf('#%d - %s', $game->getId(), $game->getSlug());
                },
            ])
            ->setCustomOption('widget', 'native')
            ->setCustomOption('valueToString', function ($game) {
                return $game ? sprintf('#%d - %s', $game->getId(), $game->getSlug()) : '';
            })
            ->formatValue(function ($value, $entity) {
                $game = $entity->getGame();
                return $game ? sprintf('#%d - %s', $game->getId(), $game->getSlug()) : '';
            }),
            AssociationField::new('user'),
            IntegerField::new('idGameApi'),
            TextField::new('name'),
            // TODO : correctement supprimer les balises HTML à la création d'une Feature
            TextEditorField::new('content')
                ->setFormTypeOption('attr', ['class' => 'ckeditor'])
                ->formatValue(function ($value) {
                    return strip_tags($value);
                }),
            ChoiceField::new('state')
                ->setChoices(FeatureState::cases())
                ->renderAsBadges()
                ->formatValue(function ($value, $entity) {
                    return $entity->getState()->value;
                }),
            DateTimeField::new('submissionDate')->hideOnForm(),
            TextField::new('slug')->hideOnForm(),
            DateTimeField::new('updatedAt')->hideOnForm(),
            AssociationField::new('images')
                ->onlyOnIndex()
                ->formatValue(function ($value, $entity) {
                    return count($entity->getImages()) . ' image(s)';
                }),
        ];
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
