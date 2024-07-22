<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Entity\Feature;
use App\Form\ImageType;
use App\Enum\FeatureState;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FeatureCrudController extends AbstractCrudController
{
    public const FEATURE_BASE_PATH = 'assets/uploads/features/';
    public const FEATURE_UPLOAD_DIR = 'public/assets/uploads/features/';

    public static function getEntityFqcn(): string
    {
        return Feature::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            yield IdField::new('id')->hideOnForm(),
            yield AssociationField::new('disability'),
            yield AssociationField::new('game')
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
                yield AssociationField::new('user'),
                yield IntegerField::new('idGameApi'),
                yield TextField::new('name'),
                yield TextEditorField::new('content')
                ->setFormTypeOption('attr', ['class' => 'ckeditor'])
                ->formatValue(function ($value) {
                    return strip_tags($value);
                }),
                yield ChoiceField::new('state')
                ->setChoices(FeatureState::cases())
                ->renderAsBadges()
                ->formatValue(function ($value, $entity) {
                    return $entity->getState()->value;
                }),
                yield DateTimeField::new('submissionDate')->hideOnForm(),
                yield TextField::new('slug')->hideOnForm(),
                yield DateTimeField::new('updatedAt')->hideOnForm(),

                // yield ImageField::new('image')
                // ->setBasePath(self::FEATURE_BASE_PATH)
                // ->setUploadDir(self::FEATURE_UPLOAD_DIR)
                // ->setUploadedFileNamePattern('[randomhash].[extension]')
                // ->setFormTypeOptions([
                //     'multiple' => true,
                //     'data_class' => Feature::class,
                //     'mapped' => false,
                //     'required' => false,
                // ]),
        ];
    }
}
