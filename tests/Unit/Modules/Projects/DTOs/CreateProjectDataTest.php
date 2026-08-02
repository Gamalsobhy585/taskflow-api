<?php

namespace Tests\Unit\Modules\Projects\DTOs;

use App\Enums\ProjectStatus;
use App\Modules\Projects\DTOs\CreateProjectData;
use PHPUnit\Framework\TestCase;

class CreateProjectDataTest extends TestCase
{
    public function test_it_creates_dto_from_array(): void
    {
        $data = CreateProjectData::fromArray(
            userId: 5,
            data: [
                'name' => 'Electro PI Assessment',
                'description' => 'Backend assessment project.',
                'status' => ProjectStatus::ACTIVE->value,
            ]
        );

        $this->assertSame(5, $data->userId);

        $this->assertSame(
            'Electro PI Assessment',
            $data->name
        );

        $this->assertSame(
            'Backend assessment project.',
            $data->description
        );

        $this->assertSame(
            ProjectStatus::ACTIVE,
            $data->status
        );
    }

    public function test_it_converts_dto_to_array(): void
    {
        $data = new CreateProjectData(
            userId: 5,
            name: 'Electro PI Assessment',
            description: 'Backend assessment project.',
            status: ProjectStatus::ACTIVE
        );

        $this->assertSame([
            'user_id' => 5,
            'name' => 'Electro PI Assessment',
            'description' => 'Backend assessment project.',
            'status' => ProjectStatus::ACTIVE->value,
        ], $data->toArray());
    }

    public function test_description_can_be_null(): void
    {
        $data = CreateProjectData::fromArray(
            userId: 5,
            data: [
                'name' => 'Project Without Description',
                'status' => ProjectStatus::ACTIVE->value,
            ]
        );

        $this->assertNull($data->description);

        $this->assertSame([
            'user_id' => 5,
            'name' => 'Project Without Description',
            'description' => null,
            'status' => ProjectStatus::ACTIVE->value,
        ], $data->toArray());
    }

    public function test_it_converts_integer_status_to_enum(): void
    {
        $data = CreateProjectData::fromArray(
            userId: 1,
            data: [
                'name' => 'Completed Project',
                'description' => null,
                'status' => ProjectStatus::COMPLETED->value,
            ]
        );

        $this->assertInstanceOf(
            ProjectStatus::class,
            $data->status
        );

        $this->assertSame(
            ProjectStatus::COMPLETED,
            $data->status
        );
    }
}