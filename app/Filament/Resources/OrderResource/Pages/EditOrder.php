<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Order;
use App\Services\OrderService;
use Filament\Notifications\Notification;
class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [

            /*
             * PENDIENTE → PROCESANDO
             */
            Actions\Action::make('startProcessing')
                ->label('Iniciar procesamiento')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('info')
                ->requiresConfirmation()
                ->visible(
                    fn(): bool =>
                        $this->record->status === Order::STATUS_PENDING
                )
                ->action(function (OrderService $orderService): void {

                    $this->record = $orderService->changeStatus(
                        $this->record,
                        Order::STATUS_PROCESSING,
                        auth()->id()
                    );

                    Notification::make()
                        ->title('Pedido en procesamiento')
                        ->success()
                        ->send();

                    $this->refreshOrderPage();
                }),


            /*
             * PROCESANDO → ENVIADO
             */
            Actions\Action::make('markAsShipped')
                ->label('Marcar como enviado')
                ->icon('heroicon-o-truck')
                ->color('primary')
                ->requiresConfirmation()
                ->visible(
                    fn(): bool =>
                        $this->record->status === Order::STATUS_PROCESSING
                )
                ->action(function (OrderService $orderService): void {

                    $this->record = $orderService->changeStatus(
                        $this->record,
                        Order::STATUS_SHIPPED,
                        auth()->id()
                    );

                    Notification::make()
                        ->title('Pedido marcado como enviado')
                        ->success()
                        ->send();

                    $this->refreshOrderPage();
                }),


            /*
             * ENVIADO → COMPLETADO
             */
            Actions\Action::make('markAsCompleted')
                ->label('Marcar como completado')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(
                    fn(): bool =>
                        $this->record->status === Order::STATUS_SHIPPED
                )
                ->action(function (OrderService $orderService): void {

                    $this->record = $orderService->changeStatus(
                        $this->record,
                        Order::STATUS_COMPLETED,
                        auth()->id()
                    );

                    Notification::make()
                        ->title('Pedido completado')
                        ->success()
                        ->send();

                    $this->refreshOrderPage();
                }),


            /*
             * CANCELACIÓN
             */
            Actions\Action::make('cancelOrder')
                ->label('Cancelar pedido')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Cancelar pedido')
                ->modalDescription(
                    'El pedido será cancelado y las unidades serán devueltas al inventario. Esta acción no se puede revertir.'
                )
                ->modalSubmitActionLabel('Sí, cancelar pedido')
                ->visible(
                    fn(): bool =>
                        in_array(
                            $this->record->status,
                            [
                                Order::STATUS_PENDING,
                                Order::STATUS_PROCESSING,
                                Order::STATUS_SHIPPED,
                            ],
                            true
                        )
                )
                ->action(function (OrderService $orderService): void {

                    $this->record = $orderService->cancel(
                        $this->record
                    );

                    Notification::make()
                        ->title('Pedido cancelado')
                        ->body(
                            'El pedido fue cancelado y el stock fue devuelto al inventario.'
                        )
                        ->success()
                        ->send();

                    $this->refreshOrderPage();
                }),
        ];
    }

    private function refreshOrderPage(): void
    {
        $this->redirect(
            static::getResource()::getUrl(
                'edit',
                ['record' => $this->record]
            )
        );
    }
}
