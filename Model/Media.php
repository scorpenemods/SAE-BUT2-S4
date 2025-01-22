//Ce fichier contient la classe Media qui permet de gérer les médias

<?php
//Class to manage medias
class Media {
    private int $id;
    private string $url;
    private string $type;
    private string $description;
    private int $displayOrder;

    public function __construct(int $id, string $url, string $type, string $description, int $displayOrder) {
        $this->id = $id;
        $this->url = $url;
        $this->type = $type;
        $this->description = $description;
        $this->displayOrder = $displayOrder;
    }

    /**
     * Get the id
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * Get the url
     * @return string
     */
    public function getUrl(): string {
        return $this->url;
    }

    /**
     * Get the type of media
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * Get the description of media
     * @return string
     */
    public function getDescription(): string {
        return $this->description;
    }

    /**
     * Get the display order of media
     * @return int
     */
    public function getDisplayOrder(): int {
        return $this->displayOrder;
    }
}