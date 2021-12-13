<?php

namespace App\Controller;

use App\Entity\Expense;
use App\Entity\Vehicle;
use App\Entity\GasStation;
use App\Form\FileUploadType;
use App\Form\FilterType;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use App\Repository\ExpenseRepository;
use App\Repository\VehicleRepository;
use App\Repository\GasStationRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExpenseController extends AbstractController
{
    private $doctrine;
    private $vehicleRepository;
    private $gasStationRepository;
    private $expenseRepository;
    private $chartBuilder;

    public function __construct(ChartBuilderInterface $chartBuilder, ManagerRegistry $doctrine, expenseRepository $expenseRepository, vehicleRepository  $vehicleRepository, gasStationRepository $gasStationRepository)
    {
        $this->doctrine             = $doctrine;
        $this->vehicleRepository    = $vehicleRepository;
        $this->gasStationRepository = $gasStationRepository;
        $this->expenseRepository    = $expenseRepository;
        $this->chartBuilder         = $chartBuilder;
    }

   /**
   * @Route("/vehicle-expenses/{plateNumber}", name="vehicle-expenses", methods={"GET", "POST"})
   */
    public function getVehicleExpenses($plateNumber,Request $request): Response
    {
        $form = $this->createForm(FilterType::class);
        $form->handleRequest($request);
        $showTotalTaxed  = null;
        $endDate         = null;
        $startDate       = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $showTotalTaxed = $form['showWithTax']->getData();
            $startDate      = $form['startDate']->getData();
            $endDate        = $form['endDate']->getData();
        } 

        $expenseByVechicleData = (object)['vehicle' => [], 'labels' => [], 'data' => []];

        if($plateNumber != null) {
           $vehicleExpense = $this->expenseRepository->findExpensesByVehicle($plateNumber,  $endDate, $startDate);
           foreach ($vehicleExpense as $key => $expense) {
               if($key == 0) {
                    $expenseByVechicleData->vehicle  = $expense['vehicle'];
                    unset($expense['vehicle']);
                    foreach ($expense as $key => $value) {
                        $expenseByVechicleData->labels [] = $key;
                    }
               } else {
                unset($expense['vehicle']);
               }

                if($showTotalTaxed == 'total_te') {
                    unset($expense['value_ti']);
                    unset($expense['tax_rate']);
                    $expense['value_te']  = $expense['value_te'].'€';
                } else {
                    unset($expense['value_te']);
                    $expense['value_ti']  = $expense['value_ti'].'€';
                    $expense['tax_rate']  = $expense['tax_rate'].'%';
                }

               $expense['issued_on'] =  $expense['issued_on']->format('d-m-Y H:i:s');
               $expenseByVechicleData->data[] = $expense;
           }

           if($showTotalTaxed == 'total_te') {
                unset($expenseByVechicleData->labels[array_search('value_ti', $expenseByVechicleData->labels)]);
                unset($expenseByVechicleData->labels[array_search('tax_rate', $expenseByVechicleData->labels)]);
            } else {
                unset($expenseByVechicleData->labels[array_search('value_te', $expenseByVechicleData->labels)]);
            }

        } 
        return $this->render('expenses\vehicleExpenses.html.twig', [
            'expenseByVechicleData'       => $expenseByVechicleData,
            'form'                        => $form->createView(),
        ]);
    }



    /**
   * @Route("/vehicle-stats", name="vehicle-stats")
   */
    public function vehicleStats(Request $request): Response
    {  
        $form = $this->createForm(FilterType::class);
        $form->handleRequest($request);
        $showTotalTaxed  = null;
        $endDate         = null;
        $startDate       = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $showTotalTaxed = $form['showWithTax']->getData();
            $startDate      = $form['startDate']->getData();
            $endDate        = $form['endDate']->getData();
        } 

        $totalExpenseByVechicleChart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $totalExpenses = $this->getTotalExpensToDisplay($endDate , $startDate ,$showTotalTaxed);
        
        if($totalExpenses != null) {
            $totalExpenseByVechicleChart->setData($this->totalExpenseByVechicleChartBuild($showTotalTaxed, $this->expenseRepository->findTotalByVehicle($endDate, $startDate)));
        }

        return $this->render('expenses\vehicleStats.html.twig', [
            'totalExpenseByVechicleChart' => $totalExpenseByVechicleChart,
            'totalExpenses'               => $totalExpenses,
            'form'                        => $form->createView(),
        ]);
    }

    private function getTotalExpensToDisplay($endDate = null, $startDate = null,$showTotalTaxed = null) {
        $totalExpenses =  current($this->expenseRepository->findTotalExpenses($endDate, $startDate));
        if($totalExpenses["total_te"] != null) {
            if($showTotalTaxed == 'total_te') {
                $totalExpenses = 'TOTAL_TE: '.$totalExpenses['total_te']."€";
            } else {
                $totalExpenses = 'TOTAL_TI: '.$totalExpenses['total_ti']."€".' (taxs: '. $totalExpenses["tax_rate"].'%)';
            }
    
            return $totalExpenses;
        } 
        return null;
    }

    private function totalExpenseByVechicleChartBuild($showTotalTaxed = null, $totalExpenseByVechicle) {
        $totalExpenseByVechicleChart = [
            'labels' => [],
            'datasets' => [
                [
                    'label'                =>  "€",
                    'type'                 => 'bar',
                    'backgroundColor' =>  [
                        'rgba(255, 99, 132)',
                        'rgba(255, 159, 64)',
                        'rgba(255, 205, 86)',
                        'rgba(75, 192, 192)',
                        'rgba(54, 162, 235)',
                        'rgba(153, 102, 255)',
                        'rgba(201, 203, 2057)',
                        'rgba(255, 99, 192)',
                        'rgba(255, 159, 65)',
                        'rgba(255, 205, 99)',
                        'rgba(54, 162, 435)',
                    ],
                    'borderColor'          => 'rgba(108,108,108)',
                    'borderWidth'          =>  1,
                    'pointBackgroundColor' => "#535353",
                    'data'                 => [],
                ],
            ],
        ];
        foreach ($totalExpenseByVechicle as $v) {
            $totalExpenseByVechicleChart['labels'][] = $v['plate_number'];
            if($showTotalTaxed == 'total_te') {
                $totalExpenseByVechicleChart['datasets'][0]['data'][] = $v['total_te'];
            } else {
                $totalExpenseByVechicleChart['datasets'][0]['data'][] = $v['total_ti'];
            }
        }
        return $totalExpenseByVechicleChart;
    }

   /**
   * @Route("/category-stats", name="category-stats")
   */
    public function categoryStats(Request $request): Response
    {  
        $form = $this->createForm(FilterType::class);
        $form->handleRequest($request);
        $showTotalTaxed  = null;
        $endDate         = null;
        $startDate       = null;
        if ($form->isSubmitted() && $form->isValid()) {
            $showTotalTaxed = $form['showWithTax']->getData();
            $startDate      = $form['startDate']->getData();
            $endDate        = $form['endDate']->getData();
        } 

        $totalExpenseByCategoryChart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $totalExpenses = $this->getTotalExpensToDisplay($endDate , $startDate, $showTotalTaxed);

        if($totalExpenses != null) {
            $totalExpenseByCategoryChart->setData($this->totalExpenseByCategoryChartBuild($showTotalTaxed ,$this->expenseRepository->findTotalByCathegory($endDate, $startDate)));
        }

        return $this->render('expenses\categoryStats.html.twig', [
            'totalExpenseByCategoryChart' => $totalExpenseByCategoryChart,
            'totalExpenses' => $totalExpenses,
            'form'          => $form->createView(),
        ]);
    }

    private function totalExpenseByCategoryChartBuild($showTotalTaxed = null,$totalExpenseByCategory) {
        $totalExpenseByCategoryChart = 
        [
            'labels' => [],
            'datasets' => [
                [
                    'label'                =>  "€",
                    'type'                 => 'doughnut',
                    'backgroundColor'      => [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 205, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(201, 203, 207, 0.2)',
                    ],
                    'hoverOffset'         => 4,
                    'data'                 => [],
                ],
            ],
        ];
        foreach ($totalExpenseByCategory as $c) {
            $totalExpenseByCategoryChart['labels'][] = $c['category'];
            if($showTotalTaxed == 'total_te') {
                $totalExpenseByCategoryChart['datasets'][0]['data'][] = $c['total_te'];
            } else {
                $totalExpenseByCategoryChart['datasets'][0]['data'][] = $c['total_ti'];
            }
        }
        return $totalExpenseByCategoryChart;
    }


   /**
   * @Route("/", name="expense_csv_upload")
   */
    public function csvUpload(Request $request):  Response
    {
        $form = $this->createForm(FileUploadType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form['upload_file']->getData();
            if($this->saveExpenseFromCsv($file)) {
                return $this->redirectToRoute('vehicle-stats');
            }
        }
        return $this->render('expenses\upload.html.twig', [
        'form' => $form->createView(),
        ]);
    }

    private function saveExpenseFromCsv($file)
    {
        try{
            $entityManager = $this->doctrine->getManager();
            $normalizers = [new ObjectNormalizer()];
            $serializer = new Serializer($normalizers);
    
            $csvArray = $this->csv_to_array($file);
            $data     = $this->buildExpenseDataFromCsv($csvArray);
            $data     = $this->buildExpenseRelationsData($data, $serializer);
    
            foreach ($data->expense as $key => $expense) {
                $e = $serializer->denormalize($expense, Expense::class);
                $e->setVehicle($data->vehicle[$expense->vehicle_plateNumber]);
                $e->setGasStation($data->gas_station[$expense->gas_station_coordinate]);
                $entityManager->persist($e);
            }
            
            $entityManager->flush();
            return true;
        }
        catch(EntityNotFoundException $e){
            error_log($e->getMessage());
            return false;
        }
    }

    private  function buildExpenseRelationsData($data, $serializer) 
    {

        $allVehicle = $this->vehicleRepository->findAll();
        foreach ($allVehicle as $key => $savedVehicle) {
            if(isset($data->vehicle[$savedVehicle->getPlateNumber()]))
                $data->vehicle[$savedVehicle->getPlateNumber()] = $savedVehicle;
        }
        foreach ($data->vehicle as $key => $vehicle) {
            if(!$vehicle instanceof Vehicle)
                $data->vehicle[$key] = $serializer->denormalize($vehicle, Vehicle::class);
        } 

        $allGasStation = $this->gasStationRepository->findAll();
        foreach ($allGasStation as $key => $savedGasStation) {
            if(isset($data->gas_station[$savedGasStation->getCoordinate()]))
                $data->gas_station[$savedGasStation->getCoordinate()] = $savedGasStation;
        }
        foreach ($data->gas_station as $key => $GasStation) {
            if(!$GasStation instanceof GasStation)
                $data->gas_station[$key] = $serializer->denormalize($GasStation, GasStation::class);
        }

        return $data;
    }

    private function buildExpenseDataFromCsv($csvArray = []) 
    {
        $data = (object)[];
        foreach ($csvArray as $key => $row) {

            if(!isset($data->vehicle[$row['Immatriculation']])) 
                $data->vehicle[$row['Immatriculation']] = (object)[];
            $data->vehicle[$row['Immatriculation']]->plateNumber = $row['Immatriculation'];
            $data->vehicle[$row['Immatriculation']]->brand        = $row['Marque'];
            $data->vehicle[$row['Immatriculation']]->modal        = $row['Model'];

            if(!isset($data->gas_station[$row['Position GPS (Latitude)'].', '.$row['Position GPS (Latitude)']])) 
                $data->gas_station[$row['Position GPS (Latitude)'].', '.$row['Position GPS (Latitude)']] = (object)[];
            $data->gas_station[$row['Position GPS (Latitude)'].', '.$row['Position GPS (Latitude)']]->description = $row['Station'];
            $data->gas_station[$row['Position GPS (Latitude)'].', '.$row['Position GPS (Latitude)']]->coordinate  = $row['Position GPS (Latitude)'].', '.$row['Position GPS (Latitude)'];

            $data->expense[$row['Code dépense']] = (object)[];
            $data->expense[$row['Code dépense']]->vehicle_plateNumber   = $row['Immatriculation'];
            $data->expense[$row['Code dépense']]->gas_station_coordinate = $row['Position GPS (Latitude)'].', '.$row['Position GPS (Latitude)'];
            $data->expense[$row['Code dépense']]->description    = $row['Libellé'];
            $data->expense[$row['Code dépense']]->category       = $row['Catégorie  de dépense'];
            $data->expense[$row['Code dépense']]->invoice_number = $row['Numéro facture'];
            $data->expense[$row['Code dépense']]->expense_number = $row['Code dépense'];
            $data->expense[$row['Code dépense']]->value_te = floatval($row['HT']);
            $data->expense[$row['Code dépense']]->tax_rate = floatval($row['TVA']);
            $data->expense[$row['Code dépense']]->value_ti = floatval($row['TTC']);
            $data->expense[$row['Code dépense']]->issued_on = new \DateTime($row['Date & heure']);
        }
        return $data;
    }

    private function csv_to_array($filename='', $delimiter=';')
    {
        if(!file_exists($filename) || !is_readable($filename))
            return FALSE;

        $header = NULL;
        $data   = [];
        if (($handle = fopen($filename, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE)
            {
                if(!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }
}
