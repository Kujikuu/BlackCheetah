<?php

namespace Tests\Feature;

use App\Models\Franchise;
use App\Models\Product;
use App\Models\Staff;
use App\Models\Task;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UnitOperationsCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $franchisee;

    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a franchisee user
        $this->franchisee = User::factory()->create([
            'role' => 'franchisee',
            'email' => 'franchisee@example.com',
        ]);

        // Create franchise and unit
        $franchise = Franchise::factory()->create([
            'franchisor_id' => User::factory()->create(['role' => 'franchisor'])->id,
        ]);

        $this->unit = Unit::factory()->create([
            'franchise_id' => $franchise->id,
            'franchisee_id' => $this->franchisee->id,
        ]);
    }

    /** @test */
    public function can_update_unit_details()
    {
        Sanctum::actingAs($this->franchisee);

        $updateData = [
            'branchName' => 'Updated Branch Name',
            'contactNumber' => '+966-123-456-789',
            'address' => 'New Address 123',
            'city' => 'New City',
            'state' => 'New State',
        ];

        $response = $this->putJson("/api/v1/unit-manager/units/details/{$this->unit->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'branchName',
                    'franchiseeName',
                    'contactNumber',
                    'address',
                    'city',
                    'state',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('units', [
            'id' => $this->unit->id,
            'unit_name' => $updateData['branchName'],
            'phone' => $updateData['contactNumber'],
        ]);
    }

    /** @test */
    public function can_create_task()
    {
        Sanctum::actingAs($this->franchisee);

        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test task description',
            'category' => 'maintenance',
            'assignedTo' => 'John Doe',
            'dueDate' => '2024-01-10',
            'priority' => 'high',
        ];

        $response = $this->postJson("/api/v1/unit-manager/units/tasks/{$this->unit->id}", $taskData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'category',
                    'assignedTo',
                    'priority',
                    'status',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('tasks', [
            'unit_id' => $this->unit->id,
            'title' => $taskData['title'],
            'priority' => $taskData['priority'],
        ]);
    }

    /** @test */
    public function can_update_task()
    {
        Sanctum::actingAs($this->franchisee);

        $task = Task::factory()->create([
            'unit_id' => $this->unit->id,
            'title' => 'Original Task',
        ]);

        $updateData = [
            'title' => 'Updated Task Title',
            'status' => 'completed',
        ];

        $response = $this->putJson("/api/v1/unit-manager/units/tasks/{$this->unit->id}/{$task->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'status',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => $updateData['title'],
            'status' => $updateData['status'],
        ]);
    }

    /** @test */
    public function can_delete_task()
    {
        Sanctum::actingAs($this->franchisee);

        $task = Task::factory()->create([
            'unit_id' => $this->unit->id,
        ]);

        $response = $this->deleteJson("/api/v1/unit-manager/units/tasks/{$this->unit->id}/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task deleted successfully',
            ]);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }

    /** @test */
    public function can_create_staff()
    {
        Sanctum::actingAs($this->franchisee);

        $staffData = [
            'name' => 'Jane Smith',
            'jobTitle' => 'Manager',
            'email' => 'jane@example.com',
            'shiftTime' => '9:00 AM - 5:00 PM',
            'hireDate' => '2024-01-15',
        ];

        $response = $this->postJson("/api/v1/unit-manager/units/staff/{$this->unit->id}", $staffData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'jobTitle',
                    'email',
                    'shiftTime',
                    'status',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('staff', [
            'name' => $staffData['name'],
            'job_title' => $staffData['jobTitle'],
            'email' => $staffData['email'],
        ]);
    }

    /** @test */
    public function can_update_staff()
    {
        Sanctum::actingAs($this->franchisee);

        $staff = Staff::factory()->create([
            'name' => 'Original Name',
        ]);
        $staff->units()->attach($this->unit->id);

        $updateData = [
            'name' => 'Updated Name',
            'status' => 'on_leave',
        ];

        $response = $this->putJson("/api/v1/unit-manager/units/staff/{$this->unit->id}/{$staff->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('staff', [
            'id' => $staff->id,
            'name' => $updateData['name'],
            'status' => $updateData['status'],
        ]);
    }

    /** @test */
    public function can_delete_staff()
    {
        Sanctum::actingAs($this->franchisee);

        $staff = Staff::factory()->create();
        $staff->units()->attach($this->unit->id);

        $response = $this->deleteJson("/api/v1/unit-manager/units/staff/{$this->unit->id}/{$staff->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Staff member removed successfully',
            ]);

        $this->assertDatabaseMissing('staff_unit', [
            'unit_id' => $this->unit->id,
            'staff_id' => $staff->id,
        ]);
    }

    /** @test */
    public function can_create_product()
    {
        Sanctum::actingAs($this->franchisee);

        // First create a franchise product
        $franchiseProduct = Product::create([
            'name' => 'Test Product',
            'description' => 'Test product description',
            'unit_price' => 29.99,
            'category' => 'Electronics',
            'status' => 'active',
            'stock' => 0,
            'franchise_id' => $this->unit->franchise_id,
            'sku' => 'SKU-TEST001',
        ]);

        // Now add it to unit inventory
        $inventoryData = [
            'productId' => $franchiseProduct->id,
            'quantity' => 100,
            'reorderLevel' => 10,
        ];

        $response = $this->postJson("/api/v1/unit-manager/units/inventory/{$this->unit->id}", $inventoryData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'unitPrice',
                    'category',
                    'stock',
                    'status',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('products', [
            'franchise_id' => $this->unit->franchise_id,
            'name' => 'Test Product',
            'unit_price' => 29.99,
        ]);

        $this->assertDatabaseHas('unit_product_inventories', [
            'unit_id' => $this->unit->id,
            'product_id' => $franchiseProduct->id,
            'quantity' => 100,
            'reorder_level' => 10,
        ]);
    }

    /** @test */
    public function can_update_product()
    {
        Sanctum::actingAs($this->franchisee);

        $product = Product::factory()->create([
            'franchise_id' => $this->unit->franchise_id,
            'name' => 'Original Product',
        ]);

        // Associate the product with the unit via inventory
        $product->units()->attach($this->unit->id, [
            'quantity' => 10,
            'reorder_level' => 5,
        ]);

        $updateData = [
            'name' => 'Updated Product Name',
            'stock' => 50,
        ];

        $response = $this->putJson("/api/v1/unit-manager/units/products/{$this->unit->id}/{$product->id}", $updateData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $updateData['name'],
            'stock' => $updateData['stock'],
        ]);
    }

    /** @test */
    public function can_delete_product()
    {
        Sanctum::actingAs($this->franchisee);

        $product = Product::factory()->create([
            'franchise_id' => $this->unit->franchise_id,
        ]);

        // Associate the product with the unit via inventory
        $product->units()->attach($this->unit->id, [
            'quantity' => 10,
            'reorder_level' => 5,
        ]);

        $response = $this->deleteJson("/api/v1/unit-manager/units/products/{$this->unit->id}/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /** @test */
    public function can_create_review()
    {
        Sanctum::actingAs($this->franchisee);

        $reviewData = [
            'customerName' => 'Happy Customer',
            'rating' => 5,
            'comment' => 'Great service!',
            'date' => '2024-01-15',
        ];

        $response = $this->postJson("/api/v1/unit-manager/units/reviews/{$this->unit->id}", $reviewData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'customerName',
                    'rating',
                    'comment',
                    'date',
                    'sentiment',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('reviews', [
            'unit_id' => $this->unit->id,
            'customer_name' => $reviewData['customerName'],
            'rating' => $reviewData['rating'],
        ]);
    }

    /** @test */
    public function can_create_document()
    {
        Sanctum::actingAs($this->franchisee);

        $documentData = [
            'title' => 'Test Document',
            'description' => 'Test document description',
            'fileName' => 'test.pdf',
            'fileSize' => '1.5 MB',
            'type' => 'Report',
        ];

        $response = $this->postJson("/api/v1/unit-manager/units/documents/{$this->unit->id}", $documentData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'fileName',
                    'fileSize',
                    'type',
                    'status',
                ],
                'message',
            ]);

        // Check that the document was added to the unit's documents JSON field
        $this->unit->refresh();
        $documents = $this->unit->documents ?? [];
        $this->assertNotEmpty($documents);
        $this->assertEquals($documentData['title'], end($documents)['title']);
    }

    /** @test */
    public function unauthorized_user_cannot_access_crud_operations()
    {
        // Test without authentication
        $response = $this->postJson("/api/v1/unit-manager/units/tasks/{$this->unit->id}", []);
        $response->assertStatus(401);

        // Test with wrong user
        $otherUser = User::factory()->create(['role' => 'franchisee']);
        Sanctum::actingAs($otherUser);

        $response = $this->postJson("/api/v1/unit-manager/units/tasks/{$this->unit->id}", []);
        $response->assertStatus(404); // Should not find unit belonging to this user
    }

    /** @test */
    public function validates_required_fields()
    {
        Sanctum::actingAs($this->franchisee);

        // Test task creation without required fields
        $response = $this->postJson("/api/v1/unit-manager/units/tasks/{$this->unit->id}", []);
        $response->assertStatus(422);

        // Test staff creation without required fields
        $response = $this->postJson("/api/v1/unit-manager/units/staff/{$this->unit->id}", []);
        $response->assertStatus(422);

        // Test inventory creation without required fields
        $response = $this->postJson("/api/v1/unit-manager/units/inventory/{$this->unit->id}", []);
        $response->assertStatus(422);
    }
}
