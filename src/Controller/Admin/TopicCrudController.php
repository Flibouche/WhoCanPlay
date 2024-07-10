<?php

namespace App\Controller\Admin;

use App\Entity\Topic;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TopicCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Topic::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
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
            TextField::new('title'),
            // TODO : attribuer un post au topic crée
            // TextEditorField::new('content'),
            BooleanField::new('isLocked'),
            TextField::new('slug')->hideOnForm(),
        ];
    }
}