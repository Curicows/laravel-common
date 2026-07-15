<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Traits;

use Curicows\LaravelCommon\Models\User;
use Curicows\LaravelCommon\Tests\Fixtures\Models\AuditedModel;
use Curicows\LaravelCommon\Tests\TestCase;
use Curicows\LaravelCommon\Traits\CreatedUpdatedBy;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CreatedUpdatedBy::class)]
class CreatedUpdatedByTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function ($table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email');
            $table->string('password')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('audited_models', function ($table): void {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        config()->set('laravel-common.models.user', User::class);
        config()->set('auth.providers.users.model', User::class);
    }

    public function test_it_sets_created_and_updated_by_when_creating_model(): void
    {
        $user = $this->authenticatedUser();

        $model = AuditedModel::query()->create(['name' => 'Tracked']);

        self::assertSame($user->id, $model->created_by);
        self::assertSame($user->id, $model->updated_by);
        self::assertTrue($model->createdBy->is($user));
        self::assertTrue($model->updatedBy->is($user));
    }

    public function test_it_does_not_override_dirty_created_or_updated_by_on_create(): void
    {
        $this->authenticatedUser();

        $model = AuditedModel::query()->create([
            'name' => 'Tracked',
            'created_by' => '8b70b087-0583-46d0-9917-858f7b06464a',
            'updated_by' => 'd1297862-474d-49cc-a410-bf80a8852f41',
        ]);

        self::assertSame('8b70b087-0583-46d0-9917-858f7b06464a', $model->created_by);
        self::assertSame('d1297862-474d-49cc-a410-bf80a8852f41', $model->updated_by);
    }

    public function test_it_keeps_auditing_fields_empty_without_authenticated_user(): void
    {
        $model = AuditedModel::query()->create(['name' => 'Tracked']);

        self::assertNull($model->created_by);
        self::assertNull($model->updated_by);
    }

    public function test_it_sets_updated_by_when_updating_model(): void
    {
        $model = AuditedModel::query()->create(['name' => 'Tracked']);
        $user = $this->authenticatedUser('updater@example.com');

        $model->name = 'Updated';
        $model->save();

        self::assertSame($user->id, $model->updated_by);
    }

    public function test_it_sets_deleted_by_when_soft_deleting_model(): void
    {
        $model = AuditedModel::query()->create(['name' => 'Tracked']);
        $user = $this->authenticatedUser('deleter@example.com');

        $model->delete();

        self::assertSame($user->id, $model->deleted_by);
        self::assertTrue($model->deletedBy->is($user));
    }

    private function authenticatedUser(string $email = 'user@example.com'): User
    {
        $user = User::query()->forceCreate([
            'id' => fake()->uuid(),
            'name' => 'Curicows',
            'email' => $email,
            'password' => 'secret',
        ]);

        $this->actingAs($user);

        return $user;
    }
}
