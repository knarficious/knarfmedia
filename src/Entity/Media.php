<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Entity\File as EmbeddedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=MediaRepository::class)
 * @ORM\Table(name="media")
 * @Vich\Uploadable
 */
class Media
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;
    
    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     * @Assert\File(
     * 		maxSize="10M",
     *          maxSizeMessage="Le fichier est trop volumineux: {{size }}. La limite est {{ limit }}",
     * 		mimeTypes={"image/png",
     *                     "image/jpeg",
     *                     "image/gif",
     *                     "image/svg+xml",
     *                     "audio/mpeg",
     *                     "audio/ogg",
     *                     "video/mp4",
     *                     "video/avi",
     *                     "video/x-msvideo"},
     *          mimeTypesMessage="Ce type de fichier n'est pas autorisé: les types autorisés sont {{ types }}",
     *          uploadErrorMessage="Le fichier ne peut pas etre téléchargé :-(")
     * @Vich\UploadableField(mapping="medias", fileNameProperty="media.name", size="media.size", mimeType="media.mimeType", dimensions="media.dimensions")
     *
     * @var File|null
     */
    private $mediaFile;
    
    /**
     * @ORM\Embedded(class="Vich\UploaderBundle\Entity\File")
     *
     * @var EmbeddedFile
     */
    private $media;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $publisedAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $size;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mimeType;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $dimensions = [];
    
    public function __construct()
    {
        $this->media = new EmbeddedFile();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPublisedAt(): ?\DateTimeImmutable
    {
        return $this->publisedAt;
    }

    public function setPublisedAt(?\DateTimeImmutable $publisedAt): self
    {
        $this->publisedAt = $publisedAt;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getDimensions(): ?array
    {
        return $this->dimensions;
    }

    public function setDimensions(?array $dimensions): self
    {
        $this->dimensions = $dimensions;

        return $this;
    }
    
    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $mediaFile
     */
    public function setMediaFile(?File $mediaFile = null): void
    {
        $this->mediaFile = $mediaFile;
        
        if (null !== $mediaFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }
    
    public function getMediaFile(): ?File
    {
        return $this->mediaFile;
    }
    
    
    public function getMedia(): ?EmbeddedFile
    {
        return $this->media;
    }
    
    public function setMedia(EmbeddedFile $media): void
    {
        $this->media = $media;
    }
}
