<?php

namespace Tests\Feature;

use App\Models\Muzakki;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MuzakkiCrudTest extends TestCase
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

    public function test_can_create_muzakki()
    {
        $muzakkiData = [
            'nama' => 'Test Muzakki',
            'nik' => '1234567890123456',
            'alamat' => 'Test Address',
            'telepon' => '081234567890',
            'email' => 'test@example.com',
            'jenis' => 'individu',
            'keterangan' => 'Test muzakki'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/muzakki', $muzakkiData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Muzakki berhasil ditambahkan'
            ]);

        $this->assertDatabaseHas('muzakki', [
            'nama' => 'Test Muzakki',
            'nik' => '1234567890123456'
        ]);
    }

    public function test_can_read_muzakki()
    {
        $muzakki = Muzakki::create([
            'nama' => 'Test Muzakki',
            'nik' => '1234567890123456',
            'alamat' => 'Test Address',
            'jenis' => 'individu'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/muzakki');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'nama', 'nik', 'alamat', 'jenis']
                    ]
                ]
            ]);
    }

    public function test_can_update_muzakki()
    {
        $muzakki = Muzakki::create([
            'nama' => 'Test Muzakki',
            'nik' => '1234567890123456',
            'alamat' => 'Test Address',
            'jenis' => 'individu'
        ]);

        $updateData = [
            'nama' => 'Updated Muzakki',
            'nik' => '1234567890123456',
            'alamat' => 'Updated Address',
            'jenis' => 'individu'
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/muzakki/' . $muzakki->id, $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Muzakki berhasil diperbarui'
            ]);

        $this->assertDatabaseHas('muzakki', [
            'id' => $muzakki->id,
            'nama' => 'Updated Muzakki'
        ]);
    }

    public function test_can_delete_muzakki()
    {
        $muzakki = Muzakki::create([
            'nama' => 'Test Muzakki',
            'nik' => '1234567890123456',
            'alamat' => 'Test Address',
            'jenis' => 'individu'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/muzakki/' . $muzakki->id);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Muzakki berhasil dihapus'
            ]);

        $this->assertDatabaseMissing('muzakki', [
            'id' => $muzakki->id
        ]);
    }

    public function test_validation_errors_on_create()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/muzakki', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => [
                    'nama',
                    'nik',
                    'alamat',
                    'jenis'
                ]
            ]);
    }
}
