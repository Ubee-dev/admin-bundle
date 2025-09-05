<?php

namespace Khalil1608\AdminBundle\Controller\Admin;

use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Khalil1608\LibBundle\Service\MediaManager;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

class MediaCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly MediaManager $mediaManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly TranslatorInterface $translator
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Media::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('admin.media.entity_singular')
            ->setEntityLabelInPlural('admin.media.entity_plural')
            ->setPageTitle('index', 'admin.media.list')
            ->setPageTitle('new', 'admin.media.new')
            ->setPageTitle('edit', 'admin.media.edit')
            ->setPageTitle('detail', 'admin.media.detail')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        $uploadAction = Action::new('upload', 'admin.media.action.upload', 'fa fa-upload')
            ->linkToCrudAction('upload')
            ->createAsGlobalAction();

        $downloadAction = Action::new('download', 'admin.media.action.download', 'fa fa-download')
            ->linkToCrudAction('download');

        $viewAction = Action::new('view', 'admin.media.action.view', 'fa fa-eye')
            ->linkToCrudAction('view');

        $editMetaAction = Action::new('editMeta', 'admin.media.action.edit_meta', 'fa fa-edit')
            ->linkToCrudAction('editMeta');

        return $actions
            ->add(Crud::PAGE_INDEX, $uploadAction)
            ->add(Crud::PAGE_INDEX, $downloadAction)
            ->add(Crud::PAGE_INDEX, $viewAction)
            ->add(Crud::PAGE_INDEX, $editMetaAction)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->disable(Action::NEW, Action::EDIT);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            TextField::new('filename', 'admin.media.field.filename')
                ->setTemplatePath('@Khalil1608Admin/media/filename.html.twig'),

            TextField::new('contentType', 'admin.media.field.content_type'),

            IntegerField::new('contentSize', 'admin.media.field.content_size')
                ->formatValue(function ($value) {
                    if ($value < 1024) return $value . ' B';
                    if ($value < 1048576) return round($value / 1024, 1) . ' KB';
                    if ($value < 1073741824) return round($value / 1048576, 1) . ' MB';
                    return round($value / 1073741824, 1) . ' GB';
                }),

            TextField::new('context', 'admin.media.field.context'),

            BooleanField::new('private', 'admin.media.field.private'),

            // Nouveau champ pour afficher les dimensions
            TextField::new('dimensionsString', 'admin.media.field.dimensions')
                ->hideOnForm()
                ->formatValue(function ($value, $entity) {
                    if ($entity->isImage() && $entity->hasDimensions()) {
                        return $entity->getDimensionsString() . ' px';
                    }
                    return $entity->isImage() ? 'N/A' : '-';
                }),

            IntegerField::new('width', 'admin.media.field.width')
                ->hideOnIndex()
                ->hideOnForm(),

            IntegerField::new('height', 'admin.media.field.height')
                ->hideOnIndex()
                ->hideOnForm(),

            TextField::new('alt', 'admin.media.field.alt')
                ->hideOnIndex(),

            TextField::new('title', 'admin.media.field.title')
                ->hideOnIndex(),

            DateTimeField::new('createdAt', 'admin.media.field.created_at')
                ->hideOnForm(),

            DateTimeField::new('updatedAt', 'admin.media.field.updated_at')
                ->hideOnForm(),
        ];
    }

    public function upload(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $uploadedFile = $request->files->get('media_file');
            $isPrivate = $request->request->getBoolean('is_private', false);
            $alt = $request->request->get('alt', '');
            $title = $request->request->get('title', '');

            if ($uploadedFile instanceof UploadedFile) {
                try {
                    $media = $this->mediaManager->upload(
                        $uploadedFile,
                        'default', // Context par défaut
                        $isPrivate
                    );

                    // Ajouter les métadonnées d'accessibilité
                    if (!empty($alt)) {
                        $media->setAlt($alt);
                    }
                    if (!empty($title)) {
                        $media->setTitle($title);
                    }

                    $this->entityManager->flush();

                    $this->addFlash('success', $this->translator->trans('admin.messages.success.uploaded', [], 'admin'));

                    $url = $this->adminUrlGenerator
                        ->setController(MediaCrudController::class)
                        ->setAction(Action::INDEX)
                        ->generateUrl();

                    return $this->redirect($url);
                } catch (\Exception $e) {
                    $this->addFlash('error', $this->translator->trans('admin.messages.error.upload_failed', [], 'admin'));
                }
            } else {
                $this->addFlash('error', $this->translator->trans('admin.messages.error.no_file_selected', [], 'admin'));
            }
        }

        return $this->render('@Khalil1608Admin/media/upload.html.twig');
    }

    public function editMeta(AdminContext $context, Request $request): Response
    {
        $media = $context->getEntity()->getInstance();

        if (!$media instanceof Media) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('POST')) {
            $alt = $request->request->get('alt', '');
            $title = $request->request->get('title', '');

            $media->setAlt($alt);
            $media->setTitle($title);

            try {
                $this->entityManager->flush();
                $this->addFlash('success', $this->translator->trans('admin.messages.success.updated', [], 'admin'));

                $url = $this->adminUrlGenerator
                    ->setController(MediaCrudController::class)
                    ->setAction(Action::INDEX)
                    ->generateUrl();

                return $this->redirect($url);
            } catch (\Exception $e) {
                $this->addFlash('error', $this->translator->trans('admin.messages.error.update_failed', [], 'admin'));
            }
        }

        try {
            $webPath = $this->mediaManager->getWebPath($media);
        } catch (\RuntimeException $e) {
            $webPath = null;
        }

        return $this->render('@Khalil1608Admin/media/edit_meta.html.twig', [
            'media' => $media,
            'webPath' => $webPath,
        ]);
    }

    public function download(AdminContext $context): Response
    {
        $media = $context->getEntity()->getInstance();

        if (!$media instanceof Media) {
            throw $this->createNotFoundException();
        }

        $filePath = $this->mediaManager->getRelativePath($media);

        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('File not found');
        }

        return $this->file($filePath, $media->getFilename());
    }

    public function view(AdminContext $context): Response
    {
        $media = $context->getEntity()->getInstance();

        if (!$media instanceof Media) {
            throw $this->createNotFoundException();
        }

        try {
            $webPath = $this->mediaManager->getWebPath($media);
            return $this->render('@Khalil1608Admin/media/view.html.twig', [
                'media' => $media,
                'webPath' => $webPath,
            ]);
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $this->translator->trans('admin.messages.error.default', [], 'admin'));
            return $this->redirectToRoute('admin_dashboard');
        }
    }

    public function delete(AdminContext $context): Response
    {
        $media = $context->getEntity()->getInstance();

        if (!$media instanceof Media) {
            throw $this->createNotFoundException();
        }

        try {
            // Supprimer le fichier physique
            $this->mediaManager->deleteAsset($media);

            // Supprimer l'entité de la base de données
            $this->mediaManager->delete($media);

            $this->addFlash('success', $this->translator->trans('admin.messages.success.deleted', [], 'admin'));
        } catch (\Exception $e) {
            $this->addFlash('error', $this->translator->trans('admin.messages.error.delete_failed', [], 'admin'));
        }

        $url = $this->adminUrlGenerator
            ->setController(__CLASS__)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }
}