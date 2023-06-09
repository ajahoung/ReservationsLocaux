<?php

namespace App\DataTables;

use App\Models\Account;
use App\Models\GroupLocal;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ReservationDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn('created_at', function ($query) {
                return date('Y/m/d', strtotime($query->created_at));
            })
            ->editColumn('status', function ($query) {
                $status = 'PENDING';
                switch ($query->status) {
                    case Reservation::ACCEPTED:
                        $status = 'primary';
                        break;
                    case Reservation::DENIED:
                        $status = 'danger';
                        break;
                    case Reservation::PENDING:
                        $status = 'dark';
                        break;
                }
                return '<span class="text-capitalize badge bg-' . $status . '">' . $query->status . '</span>';
            })
            ->editColumn('local_group.typejour', function ($query) {
                $group = GroupLocal::query()->find($query->group_local_id);
                $typejour = $group->typejours;
                return $typejour->type;
            })
            ->editColumn('local_group.typesalle', function ($query) {
                $group = GroupLocal::query()->find($query->group_local_id);
                $typesalle = $group->typesalle;
                return $typesalle->type;
            })
            ->editColumn('user.account', function ($query) {
                $account = User::query()->find($query->user_id);
                return $account->first_name . ' ' . $account->last_name;
            })
            ->addColumn('action', function ($query) {
                if ($query->status == 'PENDING') {
                    return '<div class="btn-group-sm"><a class="btn btn-sm btn-success" href="' . route('activatereservation', ['id' => $query->id, 'status' => 'ACCEPTED']) . '">Valider</a>
                 <a class="btn btn-sm btn-danger" onclick=getId("'.$query->id.'") data-bs-toggle="modal" data-bs-target="#refused-reservation">Refuser</a></div>';
                } else {
                    return '';
                }

            })
            ->rawColumns(['action', 'status']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param Reservation $model
     * @return Builder
     */
    public function query(Reservation $model)
    {
        return $model->newQuery()->with(['user', 'local', 'local_group']);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('reservation-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->orderBy(1)
            ->buttons(
                Button::make('create'),
                Button::make('export'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
            );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
            Column::make('libelle'),
            ['data' => 'user.account', 'name' => 'User', 'title' => 'User'],
            ['data' => 'start', 'name' => 'Heure debut', 'title' => 'Heure debut'],
            ['data' => 'end', 'name' => 'Heure de fin', 'title' => 'Heure de fin'],
            ['data' => 'local_group.typejour', 'name' => 'Type jour', 'title' => 'Type jour'],
            ['data' => 'local_group.typesalle', 'name' => 'Type salle', 'title' => 'Type salle'],
            ['data' => 'local.libelle', 'name' => 'Local', 'title' => 'Local'],
            ['data' => 'created_at', 'name' => 'Date creation', 'title' => 'Date creation'],
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->searchable(false)
                ->width(60)
                ->addClass('text-center hide-search'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Reservation_' . date('YmdHis');
    }
}
