<?php
// Include necessary files and logic
include 'files.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard - Coom Marketing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
            --card-border: #334155;
            --text-color: #e2e8f0;
            --text-muted: #94a3b8;
            --primary-color: #3b82f6;
            --primary-hover: #2563eb;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --nav-bg: #1a2a3a;
        }
        
        body {
            background-color: var(--dark-bg);
            color: var(--text-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: var(--nav-bg) !important;
            border-bottom: 1px solid var(--card-border);
        }
        
        .navbar-brand {
            color: var(--primary-color) !important;
            font-weight: 600;
        }
        
        .nav-link {
            color: var(--text-color) !important;
            padding: 0.5rem 1rem;
            border-radius: 4px;
        }
        
        .nav-link:hover {
            background-color: rgba(59, 130, 246, 0.1);
        }
        
        .container {
            padding-top: 2rem;
        }
        
        h2 {
            color: var(--text-color);
            border-bottom: 1px solid var(--card-border);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        
        .table {
            color: var(--text-color);
            background-color: var(--card-bg);
            border-color: var(--card-border);
        }
        
        .table th {
            background-color: var(--nav-bg);
            border-color: var(--card-border);
            color: var(--text-color);
            font-weight: 500;
        }
        
        .table td {
            border-color: var(--card-border);
            vertical-align: middle;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .btn-sm {
            padding: 0.35rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 6px;
            margin: 0.15rem;
        }
        
        .action-btns {
            white-space: nowrap;
        }
        
        .no-pending {
            color: var(--text-muted);
            padding: 1.5rem;
            text-align: center;
            background-color: var(--card-bg);
            border-radius: 8px;
            border: 1px dashed var(--card-border);
        }
        
        .table-responsive {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .navbar-nav {
                gap: 0.5rem;
                padding-top: 1rem;
            }
            
            .nav-link {
                padding: 0.5rem;
            }
            
            .btn-sm {
                padding: 0.3rem 0.6rem;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 1rem;
            }
            
            h2 {
                font-size: 1.4rem;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
            
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
    </style>
 