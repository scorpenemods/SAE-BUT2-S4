<?php

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

    public function getId(): int {
        return $this->id;
    }

    public function getUrl(): string {
        return $this->url;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getDisplayOrder(): int {
        return $this->displayOrder;
    }
}