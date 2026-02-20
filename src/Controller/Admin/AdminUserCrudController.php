<?php

namespace UbeeDev\AdminBundle\Controller\Admin;

use App\Entity\AdminUser;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use UbeeDev\AdminBundle\Form\Type\CustomNameType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminUserCrudController extends AbstractCrudController
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
    }

    public static function getEntityFqcn(): string
    {
        return AdminUser::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('admin.admin_user.entity.singular')
            ->setEntityLabelInPlural('admin.admin_user.entity.plural')
            ->setPageTitle('index', 'admin.admin_user.page.index')
            ->setPageTitle('new', 'admin.admin_user.page.new')
            ->setPageTitle('edit', 'admin.admin_user.page.edit')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->setLabel('common.id')
            ->hideOnForm();

        yield TextField::new('firstName')
            ->setLabel('admin.admin_user.field.firstName')
            ->setFormType(CustomNameType::class)
        ;

        yield TextField::new('lastName')
            ->setLabel('admin.admin_user.field.lastName')
            ->setFormType(CustomNameType::class)
        ;

        yield EmailField::new('email')
            ->setLabel('admin.admin_user.field.email');

        // Ajout du champ pour les rôles
        yield ChoiceField::new('roles')
            ->setLabel('admin.admin_user.field.roles')
            ->setChoices([
                'admin.admin_user.roles.super_admin' => 'ROLE_SUPER_ADMIN',
                'admin.admin_user.roles.admin' => 'ROLE_ADMIN',
            ])
            ->allowMultipleChoices()
            ->renderExpanded()
        ;

        // Afficher le champ de mot de passe uniquement lors de la création ou de l'édition
        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            yield TextField::new('plainPassword')
                ->setFormType(PasswordType::class)
                ->setRequired($pageName === Crud::PAGE_NEW)
                ->setHelp($pageName === Crud::PAGE_NEW
                    ? 'admin.admin_user.help.passwordNew'
                    : 'admin.admin_user.help.passwordEdit')
                ->setLabel('admin.admin_user.field.password');
        }
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('admin.admin_user.action.add');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setLabel('admin.admin_user.action.delete');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel('admin.admin_user.action.edit');
            });
    }

    /**
     * Persistance de l'entité avec hashage du mot de passe
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->hashPassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * Mise à jour de l'entité avec hashage du mot de passe si nécessaire
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->hashPassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * Hashage du mot de passe si un plainPassword est fourni
     */
    private function hashPassword(AdminUser $user): void
    {
        // Si aucun plainPassword n'est fourni, ne pas modifier le mot de passe
        if (!$user->getPlainPassword()) {
            return;
        }

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $user->getPlainPassword()
        );

        $user->setPassword($hashedPassword);
        $user->setPlainPassword(null); // Effacer le plainPassword après utilisation
    }
}