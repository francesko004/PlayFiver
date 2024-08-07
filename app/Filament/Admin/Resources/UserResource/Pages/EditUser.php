<?php

namespace App\Filament\Admin\Resources\UserResource\Pages;

use App\Filament\Admin\Resources\UserResource;
use App\Traits\Affiliates\AffiliateHistoryTrait;
use AymanAlhattami\FilamentPageWithSidebar\Traits\HasPageSidebar;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    use HasPageSidebar, AffiliateHistoryTrait;

    protected static string $resource = UserResource::class;

    /*** @param array $data
     * @return array|mixed[]
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        if(empty($data['password'])) {
            unset($data['password']);
        }

        return parent::mutateFormDataBeforeSave($data); // TODO: Change the autogenerated stub
    }

    /*** @param Model $record
     * @param array $data
     * @return Model
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if(empty($data['password'])) {
            unset($data['password']);
        }

        self::saveAffiliateHistory($record);

        $record->update($data);

        return $record;
    }

    /*** @return array|Actions\Action[]|Actions\ActionGroup[]
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
