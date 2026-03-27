{{-- Smart Recommendations Section --}}
@if(isset($recommendationStats) && $recommendationStats['total_recommendations'] > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i class="bi bi-lightbulb-fill text-warning"></i>
                        Smart Recommendations
                        <span class="badge bg-primary ms-2">{{ $recommendationStats['total_recommendations'] }} suggestions</span>
                    </h5>
                    <small class="text-muted">AI-powered suggestions based on unused and low-performing resources</small>
                </div>
                <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#recommendationsPanel">
                    <i class="bi bi-chevron-down"></i> Show/Hide
                </button>
            </div>
            <div class="collapse show" id="recommendationsPanel">
                <div class="card-body pt-0">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="bg-danger bg-opacity-10 rounded p-3 text-center">
                                <h4 class="text-danger mb-0">{{ $recommendationStats['total_unused'] }}</h4>
                                <small class="text-muted">Never Used</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-warning bg-opacity-10 rounded p-3 text-center">
                                <h4 class="text-warning mb-0">{{ $recommendationStats['total_never_sold'] }}</h4>
                                <small class="text-muted">Never Sold</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-info bg-opacity-10 rounded p-3 text-center">
                                <h4 class="text-info mb-0">{{ $recommendationStats['total_low_performing'] }}</h4>
                                <small class="text-muted">Low Performing</small>
                            </div>
                        </div>
                    </div>

                    @if($recommendations['suggested_combo']['has_suggestions'])
                    <div class="alert alert-success mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong><i class="bi bi-magic"></i> Suggested Package Combination</strong>
                            <button type="button" class="btn btn-success btn-sm" onclick="applySuggestedCombo()">
                                <i class="bi bi-check-all"></i> Apply All Suggestions
                            </button>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($recommendations['suggested_combo']['items'] as $type => $item)
                            <span class="badge bg-white text-dark border">
                                <i class="bi bi-{{ $type == 'boat' ? 'water' : ($type == 'homestay' ? 'house' : 'geo-alt') }}"></i>
                                {{ $item['name'] }}
                                <small class="text-muted">({{ $item['formatted_price'] }})</small>
                            </span>
                            @endforeach
                        </div>
                        @if($recommendations['suggested_combo']['estimated_cost'] > 0)
                        <small class="text-muted mt-2 d-block">Estimated Cost: RM {{ number_format($recommendations['suggested_combo']['estimated_cost'], 2) }}</small>
                        @endif
                    </div>
                    @endif

                    <ul class="nav nav-tabs nav-fill" id="recTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#rec-boats">
                                <i class="bi bi-water"></i> Boats
                                <span class="badge bg-secondary">{{ count($recommendations['boats']['never_used']) + count($recommendations['boats']['never_sold']) }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rec-homestays">
                                <i class="bi bi-house"></i> Homestays
                                <span class="badge bg-secondary">{{ count($recommendations['homestays']['never_used']) + count($recommendations['homestays']['never_sold']) }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rec-destinations">
                                <i class="bi bi-geo-alt"></i> Destinations
                                <span class="badge bg-secondary">{{ count($recommendations['destinations']['never_used']) + count($recommendations['destinations']['never_sold']) }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rec-culinaries">
                                <i class="bi bi-cup-hot"></i> Culinaries
                                <span class="badge bg-secondary">{{ count($recommendations['culinaries']['never_used']) + count($recommendations['culinaries']['never_sold']) }}</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rec-kiosks">
                                <i class="bi bi-shop"></i> Kiosks
                                <span class="badge bg-secondary">{{ count($recommendations['kiosks']['never_used']) + count($recommendations['kiosks']['never_sold']) }}</span>
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content p-3 bg-light rounded-bottom">
                        <div class="tab-pane fade show active" id="rec-boats">
                            @if(count($recommendations['boats']['never_used']) > 0 || count($recommendations['boats']['never_sold']) > 0)
                            <div class="row g-2">
                                @foreach($recommendations['boats']['never_used'] as $item)
                                <div class="col-md-4">
                                    <div class="card h-100 border-danger">
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong class="small">{{ $item['name'] }}</strong>
                                                    <br><span class="badge bg-danger">{{ $item['reason'] }}</span>
                                                    <br><small class="text-success">{{ $item['formatted_price'] }}</small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('boat', {{ $item['id'] }})">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @foreach($recommendations['boats']['never_sold'] as $item)
                                <div class="col-md-4">
                                    <div class="card h-100 border-warning">
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong class="small">{{ $item['name'] }}</strong>
                                                    <br><span class="badge bg-warning text-dark">{{ $item['reason'] }}</span>
                                                    <br><small class="text-success">{{ $item['formatted_price'] }}</small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('boat', {{ $item['id'] }})">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-muted text-center mb-0"><i class="bi bi-check-circle text-success"></i> All boats are being utilized well!</p>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="rec-homestays">
                            @if(count($recommendations['homestays']['never_used']) > 0 || count($recommendations['homestays']['never_sold']) > 0)
                            <div class="row g-2">
                                @foreach($recommendations['homestays']['never_used'] as $item)
                                <div class="col-md-4">
                                    <div class="card h-100 border-danger">
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong class="small">{{ $item['name'] }}</strong>
                                                    <br><span class="badge bg-danger">{{ $item['reason'] }}</span>
                                                    <br><small class="text-success">{{ $item['formatted_price'] }}/night</small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('homestay', {{ $item['id'] }})">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @foreach($recommendations['homestays']['never_sold'] as $item)
                                <div class="col-md-4">
                                    <div class="card h-100 border-warning">
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong class="small">{{ $item['name'] }}</strong>
                                                    <br><span class="badge bg-warning text-dark">{{ $item['reason'] }}</span>
                                                    <br><small class="text-success">{{ $item['formatted_price'] }}/night</small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('homestay', {{ $item['id'] }})">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-muted text-center mb-0"><i class="bi bi-check-circle text-success"></i> All homestays are being utilized well!</p>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="rec-destinations">
                            @if(count($recommendations['destinations']['never_used']) > 0 || count($recommendations['destinations']['never_sold']) > 0)
                            <div class="row g-2">
                                @foreach($recommendations['destinations']['never_used'] as $item)
                                <div class="col-md-4">
                                    <div class="card h-100 border-danger">
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong class="small">{{ $item['name'] }}</strong>
                                                    <br><span class="badge bg-danger">{{ $item['reason'] }}</span>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('destination', {{ $item['id'] }})">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @foreach($recommendations['destinations']['never_sold'] as $item)
                                <div class="col-md-4">
                                    <div class="card h-100 border-warning">
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong class="small">{{ $item['name'] }}</strong>
                                                    <br><span class="badge bg-warning text-dark">{{ $item['reason'] }}</span>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('destination', {{ $item['id'] }})">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-muted text-center mb-0"><i class="bi bi-check-circle text-success"></i> All destinations are being utilized well!</p>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="rec-culinaries">
                            @if(count($recommendations['culinaries']['never_used']) > 0 || count($recommendations['culinaries']['never_sold']) > 0)
                            <div class="row g-2">
                                @foreach($recommendations['culinaries']['never_used'] as $item)
                                <div class="col-md-4">
                                    <div class="card h-100 border-danger">
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong class="small">{{ $item['name'] }}</strong>
                                                    <br><span class="badge bg-danger">{{ $item['reason'] }}</span>
                                                    <br><small class="text-success">{{ $item['formatted_price'] }}</small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('culinary', {{ $item['id'] }})">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @foreach($recommendations['culinaries']['never_sold'] as $item)
                                <div class="col-md-4">
                                    <div class="card h-100 border-warning">
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong class="small">{{ $item['name'] }}</strong>
                                                    <br><span class="badge bg-warning text-dark">{{ $item['reason'] }}</span>
                                                    <br><small class="text-success">{{ $item['formatted_price'] }}</small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('culinary', {{ $item['id'] }})">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-muted text-center mb-0"><i class="bi bi-check-circle text-success"></i> All culinary items are being utilized well!</p>
                            @endif
                        </div>
                        <div class="tab-pane fade" id="rec-kiosks">
                            @if(count($recommendations['kiosks']['never_used']) > 0 || count($recommendations['kiosks']['never_sold']) > 0)
                            <div class="row g-2">
                                @foreach($recommendations['kiosks']['never_used'] as $item)
                                <div class="col-md-4">
                                    <div class="card h-100 border-danger">
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong class="small">{{ $item['name'] }}</strong>
                                                    <br><span class="badge bg-danger">{{ $item['reason'] }}</span>
                                                    <br><small class="text-success">{{ $item['formatted_price'] }}</small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('kiosk', {{ $item['id'] }})">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                @foreach($recommendations['kiosks']['never_sold'] as $item)
                                <div class="col-md-4">
                                    <div class="card h-100 border-warning">
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <strong class="small">{{ $item['name'] }}</strong>
                                                    <br><span class="badge bg-warning text-dark">{{ $item['reason'] }}</span>
                                                    <br><small class="text-success">{{ $item['formatted_price'] }}</small>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRecommendedItem('kiosk', {{ $item['id'] }})">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <p class="text-muted text-center mb-0"><i class="bi bi-check-circle text-success"></i> All kiosks are being utilized well!</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
