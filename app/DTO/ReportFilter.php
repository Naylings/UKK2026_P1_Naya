<?php

namespace App\DTO;

class ReportFilter
{
    public ?string $startDate = null;
    public ?string $endDate = null;
    public ?string $userId = null;
    public ?string $toolId = null;
    public ?string $toolName = null;
    public ?string $unitCode = null;
    public ?string $status = null;
    public ?string $categoryId = null;
    public ?string $search = null;

    public static function fromArray(array $data): self
    {
        $f = new self();
        $f->startDate = $data['start_date'] ?? $data['startDate'] ?? null;
        $f->endDate = $data['end_date'] ?? $data['endDate'] ?? null;
        $f->userId = $data['user_id'] ?? $data['userId'] ?? null;
        $f->toolId = $data['tool_id'] ?? $data['toolId'] ?? null;
        $f->toolName = $data['tool_name'] ?? $data['toolName'] ?? null;
        $f->unitCode = $data['unit_code'] ?? $data['unitCode'] ?? null;
        $f->status = $data['status'] ?? null;
        $f->categoryId = $data['category_id'] ?? $data['categoryId'] ?? null;
        $f->search = $data['search'] ?? null;
        return $f;
    }
}
