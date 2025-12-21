<?php

namespace App\Controller\Admin;

use App\Entity\Produit;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;

class ProduitCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Produit::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('reference', 'Référence'),
            TextField::new('nom', 'Nom du produit'),
            TextareaField::new('description'),
            MoneyField::new('prix')->setCurrency('TND'),
            MoneyField::new('prixPromo')->setCurrency('%'),
            IntegerField::new('stock'),
            BooleanField::new('estDisponible', 'Disponible ?'),
            DateTimeField::new('dateCreation', 'Date de création'),
            TextField::new('image', 'Image')->onlyOnIndex(),

            FormField::addPanel('Image Upload'),
            TextField::new('imageFile', 'Image (Upload)')
                ->setFormType(FileType::class)
                ->onlyOnForms(),
        ];
    }
}
