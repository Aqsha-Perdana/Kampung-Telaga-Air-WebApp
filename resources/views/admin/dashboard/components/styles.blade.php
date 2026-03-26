  <style>
  .round-40 {
    width: 40px;
    height: 40px;
  }

  .timeline-badge {
    width: 10px;
    height: 10px;
    border-radius: 50%;
  }

  .timeline-item:not(:last-child) .timeline-badge-wrap::after {
    content: '';
    position: absolute;
    width: 2px;
    height: 100%;
    background: #e9ecef;
    left: 4px;
    top: 10px;
  }

  .nav-pills .nav-link {
    border-radius: 0.25rem;
    margin-right: 0.5rem;
  }

  .nav-pills .nav-link.active {
    background-color: #5D87FF;
  }

  .table-danger {
    background-color: rgba(250, 137, 107, 0.1);
  }

  /* Custom Pagination Styling */
  .pagination {
    margin-bottom: 0;
  }

  .page-link {
    color: #5D87FF;
    border-color: #dee2e6;
  }

  .page-link:hover {
    color: #4556d6;
    background-color: #e9ecef;
    border-color: #dee2e6;
  }

  .page-item.active .page-link {
    background-color: #5D87FF;
    border-color: #5D87FF;
  }

  .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
  }

  /* Unsold Resources Accordion */
  .unsold-accordion .accordion-button {
    background: transparent;
    font-size: 0.9rem;
    box-shadow: none !important;
  }
  .unsold-accordion .accordion-button:not(.collapsed) {
    background: rgba(99, 102, 241, 0.05);
    color: #333;
  }
  .unsold-accordion .accordion-button::after {
    width: 1rem;
    height: 1rem;
    background-size: 1rem;
  }
  .unsold-accordion .accordion-item {
    border-color: #e9ecef !important;
  }
  .unsold-accordion .table th {
    font-size: 0.78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.02em;
    color: #6c757d;
  }
  .unsold-accordion .table td {
    font-size: 0.85rem;
    padding-top: 0.4rem;
    padding-bottom: 0.4rem;
  }
  </style>
