<?php

namespace Database\Seeders;

use App\Models\QueueTicket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class QueueSeeder extends Seeder
{
    /**
     * Twelve tickets for today: four already served, one being served, one
     * freshly called at Window 1 and six still waiting in line.
     *
     * Ticket numbers follow QueueTicket::nextNumber() formatting (A-001 …).
     */
    public function run(): void
    {
        $residents = User::query()->where('role', 'resident')->orderBy('id')->get();

        if ($residents->isEmpty()) {
            return; // UserSeeder must run first.
        }

        $today = Carbon::today();

        $rows = [
            ['ticket_no' => 'A-001', 'name' => $residents[0]->name, 'user_id' => $residents[0]->id, 'service' => 'document_request', 'window' => 'Window 1', 'status' => 'done',    'called_at' => now()->subMinutes(140), 'served_at' => now()->subMinutes(134)],
            ['ticket_no' => 'A-002', 'name' => $residents[1]->name, 'user_id' => $residents[1]->id, 'service' => 'payment',         'window' => 'Window 2', 'status' => 'done',    'called_at' => now()->subMinutes(120), 'served_at' => now()->subMinutes(113)],
            ['ticket_no' => 'A-003', 'name' => $residents[2]->name, 'user_id' => $residents[2]->id, 'service' => 'general',         'window' => 'Window 1', 'status' => 'done',    'called_at' => now()->subMinutes(95),  'served_at' => now()->subMinutes(88)],
            ['ticket_no' => 'A-004', 'name' => $residents[3]->name, 'user_id' => $residents[3]->id, 'service' => 'consultation',    'window' => 'Window 2', 'status' => 'done',    'called_at' => now()->subMinutes(70),  'served_at' => now()->subMinutes(63)],
            ['ticket_no' => 'A-005', 'name' => $residents[4]->name, 'user_id' => $residents[4]->id, 'service' => 'complaint',       'window' => 'Window 2', 'status' => 'serving', 'called_at' => now()->subMinutes(18),  'served_at' => null],
            ['ticket_no' => 'A-006', 'name' => $residents[5]->name, 'user_id' => $residents[5]->id, 'service' => 'document_request','window' => 'Window 1', 'status' => 'called',  'called_at' => now()->subMinutes(5),   'served_at' => null],
            ['ticket_no' => 'A-007', 'name' => $residents[0]->name, 'user_id' => $residents[0]->id, 'service' => 'payment',         'window' => null,       'status' => 'waiting', 'called_at' => null, 'served_at' => null],
            ['ticket_no' => 'A-008', 'name' => $residents[2]->name, 'user_id' => $residents[2]->id, 'service' => 'general',         'window' => null,       'status' => 'waiting', 'called_at' => null, 'served_at' => null],
            ['ticket_no' => 'A-009', 'name' => $residents[1]->name, 'user_id' => $residents[1]->id, 'service' => 'document_request','window' => null,       'status' => 'waiting', 'called_at' => null, 'served_at' => null],
            ['ticket_no' => 'A-010', 'name' => $residents[3]->name, 'user_id' => $residents[3]->id, 'service' => 'consultation',    'window' => null,       'status' => 'waiting', 'called_at' => null, 'served_at' => null],
            ['ticket_no' => 'A-011', 'name' => $residents[5]->name, 'user_id' => $residents[5]->id, 'service' => 'complaint',       'window' => null,       'status' => 'waiting', 'called_at' => null, 'served_at' => null],
            ['ticket_no' => 'A-012', 'name' => $residents[4]->name, 'user_id' => $residents[4]->id, 'service' => 'general',         'window' => null,       'status' => 'waiting', 'called_at' => null, 'served_at' => null],
        ];

        foreach ($rows as $index => $row) {
            $takenAt = now()->subMinutes(160 - ($index * 10));

            $ticket = QueueTicket::query()->firstOrCreate(
                ['queue_date' => $today->toDateString(), 'ticket_no' => $row['ticket_no']],
                [
                    'user_id'        => $row['user_id'],
                    'name_on_ticket' => $row['name'],
                    'service'        => $row['service'],
                    'window'         => $row['window'],
                    'status'         => $row['status'],
                    'called_at'      => $row['called_at'],
                    'served_at'      => $row['served_at'],
                ]
            );

            $ticket->forceFill(['created_at' => $takenAt, 'updated_at' => $ticket->served_at ?? $ticket->called_at ?? $takenAt])->save();
        }
    }
}
