<?php

namespace App\Filament\Admin\Resources\SettingResource\Pages;

use App\Filament\Admin\Resources\SettingResource;
use App\Models\Setting;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GatewayPage extends Page implements HasForms
{
    use HasPageSidebar, InteractsWithForms;

    protected static string $resource = SettingResource::class;

    protected static string $view = 'filament.resources.setting-resource.pages.gateway-page';

    /*** @return string|Htmlable
     */
    public function getTitle(): string | Htmlable
    {
        return 'Gateways';
    }

    public Setting $record;
    public ?array $data = [];

    /**
     * @param Model $record
     * @return bool
     */
    public static function canView(Model $record): bool
    {
        return auth()->user()->hasRole('admin');
    }

    /**
     * @return void
     */
    public function mount(): void
    {
        $setting = Setting::first();
        $this->record = $setting;
        $this->form->fill($setting->toArray());
    }

    /**
     * @return void
     */
    public function save()
    {
        try {
            if (env('APP_DEMO')) {
                Notification::make()
                    ->title('Atenção')
                    ->body('Você não pode realizar está alteração na versão demo')
                    ->danger()
                    ->send();
                return;
            }

            $setting = Setting::find($this->record->id);

            if ($setting->update($this->data)) {
                Cache::put('setting', $setting);

                Notification::make()
                    ->title('Dados alterados')
                    ->body('Dados alterados com sucesso!')
                    ->success()
                    ->send();

                redirect(route('filament.admin.resources.settings.gateway', ['record' => $this->record->id]));
            }
        } catch (Halt $exception) {
            return;
        }
    }

    /**
     * @param Form $form
     * @return Form
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Gateways')
                    ->description('Ativa ou desative seus gateway de Pagamento')
                    ->schema([
                        Select::make('default_gateway')
                            ->label('Gateway Padrão para Saque')
                            ->options([
                                'suitpay' => 'Suitpay',
                                'digitopay' => 'Digitopay',
                                'ezzepay' => "EzzePay"
                            ])->columnSpanFull(),

                        Toggle::make('suitpay_is_enable')
                            ->label('SuitPay Ativo'),
                        Toggle::make('digitopay_is_enable')
                            ->label('DigitoPay'),
                        Toggle::make('ezzepay_is_enable')
                            ->label('EzzePay'),
                        Toggle::make('withdrawal_autom')
                            ->label("Saque automático"),
                        TextInput::make('limit_withdrawal')
                            ->label('Limite de Saque automático ')
                            ->numeric(),

                    ])->columns(2)
            ])
            ->statePath('data');
    }
}
