@extends('layout.sidebar')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Operating Expenses Detail</h3>
                </div>

                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Transaction Code</th>
                            <td>{{ $bebanOperasional->kode_transaksi }}</td>
                        </tr>
                        <tr>
                            <th>Date</th>
                            <td>{{ $bebanOperasional->tanggal->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <th>Category</th>
                            <td>
                                <span class="badge badge-secondary">{{ $bebanOperasional->kategori }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $bebanOperasional->deskripsi }}</td>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <td class="text-danger">
                                <strong>{{ format_ringgit($bebanOperasional->jumlah) }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <th>Payment Method</th>
                            <td>{{ $bebanOperasional->metode_pembayaran }}</td>
                        </tr>
                        <tr>
                            <th>No Ref</th>
                            <td>{{ $bebanOperasional->nomor_referensi ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $bebanOperasional->keterangan ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Proof of Payment</th>
                            <td>
                                @if($bebanOperasional->bukti_pembayaran)
                                    <a href="{{ Storage::url($bebanOperasional->bukti_pembayaran) }}" 
                                       target="_blank" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-file"></i> View Proof
                                    </a>
                                @else
                                    <span class="text-muted">No evidence</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Created</th>
                            <td>{{ $bebanOperasional->created_at->format('d F Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last update</th>
                            <td>{{ $bebanOperasional->updated_at->format('d F Y H:i') }}</td>
                        </tr>
                    </table>
                </div>

                <div class="card-footer">
                    <a href="{{ route('beban-operasional.edit', $bebanOperasional) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('beban-operasional.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                    <form action="{{ route('beban-operasional.destroy', $bebanOperasional) }}" 
                          method="POST" 
                          class="d-inline"
                          onsubmit="event.preventDefault(); adminDeleteSwal({ actionUrl: '{{ route('beban-operasional.destroy', $bebanOperasional) }}', itemLabel: @js($bebanOperasional->nama ?? 'this record'), title: 'Delete Expense?', html: 'This will permanently delete <strong>' + @js($bebanOperasional->nama ?? 'this record') + '</strong>. This action cannot be undone.' });">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
