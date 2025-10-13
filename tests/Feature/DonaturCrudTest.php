<?php

namespace Tests\Feature;

use App\Models\Donatur;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DonaturCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin role
        $role = Role::create(['name' => 'admin', 'display_name' => 'Administrator']);
        
        // Create admin user
        $this->user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'status' => 'active'
        ]);
    }

    public function test_can_create_donatur()
    {
        $donaturData = [
            'nama' => 'Test Donatur',
            'nik' => '1234567890123456',
            'alamat' => 'Test Address',
            'telepon' => '081234567890',
            'email' => 'test@example.com',
            'jenis_donatur' => 'individu',
            'keterangan' => 'Test donatur'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/donatur', $donaturData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Donatur berhasil ditambahkan'
            ]);

        $this->assertDatabaseHas('donatur', [
            'nama' => 'Test Donatur',
            'nik' => '1234567890123456'
        ]);
    }

    public function test_can_read_donatur()
    {
        $donatur = Donatur::create([
            'nama' => 'Test Donatur',
            'nik' => '1234567890123456',
            'alamat' => 'Test Address',
            'jenis_donatur' => 'individu'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/donatur');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'nama', 'nik', 'alamat', 'jenis_donatur']
                    ]
                ]
            ]);
    }

    public function test_can_update_donatur()
    {
        $donatur = Donatur::create([
            'nama' => 'Test Donatur',
            'nik' => '1234567890123456',
            'alamat' => 'Test Address',
            'jenis_donatur' => 'individu'
        ]);

        $updateData = [
            'nama' => 'Updated Donatur',
            'nik' => '1234567890123456',
            'alamat' => 'Updated Address',
            'jenis_donatur' => 'individu'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/donatur/' . $donatur->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Donatur berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('donatur', [
            'id' => $donatur->id,
            'nama' => 'Updated Donatur'
        ]);
    }

    public function test_can_delete_donatur()
    {
        $donatur = Donatur::create([
            'nama' => 'Test Donatur',
            'nik' => '1234567890123456',
            'alamat' => 'Test Address',
            'jenis_donatur' => 'individu'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/donatur/' . $donatur->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Donatur berhasil dihapus'
            ]);

        $this->assertDatabaseMissing('donatur', [
            'id' => $donatur->id
        ]);
    }

    public function test_validation_errors_on_create()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/donatur', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'nama',
                    'nik',
                    'alamat',
                    'jenis_donatur'
                ]
            ]);
    }
}