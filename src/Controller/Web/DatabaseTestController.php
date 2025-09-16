<?php

namespace App\Controller\Web;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/database-test', name: 'admin_database_test_')]
class DatabaseTestController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Connection $connection
    ) {}

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        // Get database statistics
        $tables = $this->getDatabaseTables();
        $stats = $this->getDatabaseStats();
        
        return $this->render('admin/database_test/index.html.twig', [
            'tables' => $tables,
            'stats' => $stats,
        ]);
    }

    #[Route('/activity-monitor', name: 'activity_monitor')]
    public function activityMonitor(): Response
    {
        return $this->render('admin/database_test/activity_monitor.html.twig');
    }

    #[Route('/table-explorer/{tableName?}', name: 'table_explorer')]
    public function tableExplorer(?string $tableName = null): Response
    {
        $tables = $this->getDatabaseTables();
        $tableData = null;
        $tableInfo = null;

        if ($tableName) {
            $tableData = $this->getTableData($tableName);
            $tableInfo = $this->getTableInfo($tableName);
        }

        return $this->render('admin/database_test/table_explorer.html.twig', [
            'tables' => $tables,
            'selectedTable' => $tableName,
            'tableData' => $tableData,
            'tableInfo' => $tableInfo,
        ]);
    }

    #[Route('/sql-console', name: 'sql_console')]
    public function sqlConsole(): Response
    {
        return $this->render('admin/database_test/sql_console.html.twig');
    }

    #[Route('/api/execute-query', name: 'api_execute_query', methods: ['POST'])]
    public function executeQuery(Request $request): JsonResponse
    {
        $query = $request->request->get('query');
        
        if (empty($query)) {
            return new JsonResponse(['error' => 'Query is required'], 400);
        }

        try {
            // Security check - only allow SELECT queries for safety
            if (!preg_match('/^\s*SELECT/i', trim($query))) {
                return new JsonResponse(['error' => 'Only SELECT queries are allowed'], 400);
            }

            $result = $this->connection->executeQuery($query);
            $data = $result->fetchAllAssociative();
            
            return new JsonResponse([
                'success' => true,
                'data' => $data,
                'rowCount' => count($data),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    #[Route('/api/table-data/{tableName}', name: 'api_table_data', methods: ['GET'])]
    public function getTableDataApi(string $tableName, Request $request): JsonResponse
    {
        $page = max(1, (int)$request->query->get('page', 1));
        $limit = min(100, max(10, (int)$request->query->get('limit', 20)));
        $offset = ($page - 1) * $limit;

        try {
            $sql = "SELECT * FROM `{$tableName}` LIMIT {$limit} OFFSET {$offset}";
            $result = $this->connection->executeQuery($sql);
            $data = $result->fetchAllAssociative();

            // Get total count
            $countResult = $this->connection->executeQuery("SELECT COUNT(*) as total FROM `{$tableName}`");
            $total = $countResult->fetchOne();

            return new JsonResponse([
                'data' => $data,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit),
                ],
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/database-stats', name: 'api_database_stats', methods: ['GET'])]
    public function getDatabaseStatsApi(): JsonResponse
    {
        try {
            $stats = $this->getDatabaseStats();
            return new JsonResponse($stats);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    private function getDatabaseTables(): array
    {
        try {
            $sql = "SHOW TABLES";
            $result = $this->connection->executeQuery($sql);
            $tables = [];
            
            while ($row = $result->fetchNumeric()) {
                $tableName = $row[0];
                
                // Get row count for each table
                $countResult = $this->connection->executeQuery("SELECT COUNT(*) FROM `{$tableName}`");
                $rowCount = $countResult->fetchOne();
                
                $tables[] = [
                    'name' => $tableName,
                    'rows' => $rowCount,
                ];
            }
            
            return $tables;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getDatabaseStats(): array
    {
        try {
            $stats = [];
            
            // Database size
            $sizeResult = $this->connection->executeQuery(
                "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
                 FROM information_schema.tables 
                 WHERE table_schema = DATABASE()"
            );
            $stats['size_mb'] = $sizeResult->fetchOne() ?: 0;
            
            // Table count
            $tableCountResult = $this->connection->executeQuery(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()"
            );
            $stats['table_count'] = $tableCountResult->fetchOne();
            
            // Total rows across all tables
            $totalRows = 0;
            $tables = $this->getDatabaseTables();
            foreach ($tables as $table) {
                $totalRows += $table['rows'];
            }
            $stats['total_rows'] = $totalRows;
            
            // Connection info
            $stats['database_name'] = $this->connection->getDatabase();
            
            return $stats;
        } catch (\Exception $e) {
            return [
                'size_mb' => 0,
                'table_count' => 0,
                'total_rows' => 0,
                'database_name' => '',
            ];
        }
    }

    private function getTableData(string $tableName): array
    {
        try {
            $sql = "SELECT * FROM `{$tableName}` LIMIT 100";
            $result = $this->connection->executeQuery($sql);
            return $result->fetchAllAssociative();
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getTableInfo(string $tableName): array
    {
        try {
            $sql = "DESCRIBE `{$tableName}`";
            $result = $this->connection->executeQuery($sql);
            return $result->fetchAllAssociative();
        } catch (\Exception $e) {
            return [];
        }
    }
}