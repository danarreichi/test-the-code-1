<?php
class RestaurantQueue
{
    private $tableType = [2, 4, 6];
    public function addToQueue($name, $qtyPerson)
    {
        $jsonData = file_get_contents('queue.json');
        $guestQueue = json_decode($jsonData, true);

        // Check if the queue is empty, if so, initialize it as an empty array
        if ($guestQueue === null) {
            $guestQueue = (object) [];
        }

        date_default_timezone_set('Asia/Jakarta');
        $guest = [
            "name" => $name,
            "qty" => $qtyPerson,
            "time_created" => date('H:i:s'),
            "assigned_to" => [] // Initialize 'assigned_to' as an empty array
        ];
        $key = count($guestQueue) + 1; // Incremental key

        $guestQueue[$key] = $guest ?? (object) array();

        $jsonData = json_encode($guestQueue, JSON_PRETTY_PRINT);

        file_put_contents("queue.json", $jsonData);
    }


    public function assignTable(array $guest, array $availableTable, int $key)
    {
        $guestQty = $guest["qty"];
        $usedTable = $this->findClosestCombination($availableTable, $guestQty);
        $assignedTo = [];
        foreach ($usedTable as $table) {
            array_push($assignedTo, [
                "table" => $table["table"],
                "qty" => $table["qty"],
                "time_created" => date('H:i:s')
            ]);
        }
        $guest["assigned_to"] = $assignedTo;

        $jsonData = file_get_contents('queue.json');
        $guestQueue = json_decode($jsonData, true);
        $guestQueue[$key] = $guest;
        $jsonData = json_encode($guestQueue, JSON_PRETTY_PRINT);
        file_put_contents("queue.json", $jsonData);
    }

    public function viewQueue()
    {
        $jsonData = file_get_contents('queue.json');
        $dataArray = json_decode($jsonData, true);
        $this->timeCheck();
        return $dataArray;
    }

    public function timeCheck()
    {
        $jsonData = file_get_contents('queue.json');
        $dataArray = json_decode($jsonData, true);
        if ($dataArray === null) {
            $dataArray = (object) [];
        }
        date_default_timezone_set('Asia/Jakarta');
        $currentTime = date('H:i:s');
        foreach ($dataArray as $index => &$queue) {
            if (isset($queue["assigned_to"])) {
                $assignedTo = $queue["assigned_to"];
                foreach ($assignedTo as $key => &$value) {
                    $secondsDiff = abs(strtotime($currentTime) - strtotime($value['time_created']));
                    $minutesDiff = $secondsDiff / 60;
                    if ($minutesDiff > 10) {
                        $queue["qty"] = (int) $queue["qty"] - (int) $value["qty"];
                        unset($assignedTo[$key]);
                    }
                }
                $queue["assigned_to"] = $assignedTo;
                if ($queue["qty"] == 0) unset($dataArray[$index]);
            }
        }
        $jsonData = json_encode($dataArray, JSON_PRETTY_PRINT);
        file_put_contents("queue.json", $jsonData);
        return $dataArray;
    }

    public function callNext()
    {
        $this->timeCheck();
        $jsonData = file_get_contents('queue.json');
        $guestQueue = json_decode($jsonData, true);
        $next = null;
        $assignedTo = [];
        foreach ($guestQueue as $key => $item) {
            if (isset($item["assigned_to"])) {
                foreach (array_column($item["assigned_to"], "table") as $table) {
                    array_push($assignedTo, $table);
                }
                $totalAssigned = array_sum(array_column($item["assigned_to"], "qty"));
                if ($totalAssigned < $item["qty"]) {
                    $next = $key;
                    break;
                }
            } else {
                $next = $key;
                break;
            }
            if (empty(array_diff($this->tableType, $assignedTo))) break;
        }
        $availableTable = array_diff($this->tableType, $assignedTo);
        if ($next && ($guestQueue[$next]["qty"] <= array_sum($availableTable))) {
            $this->assignTable($guestQueue[$next], $availableTable, $next);
            return $this->viewQueue();
        }
        return $this->viewQueue();
    }

    private function findClosestCombination($array, $target)
    {
        rsort($array);
        $result = [];

        foreach ($array as $item) {
            if ($target > 0) {
                array_push($result, [
                    "table" => $item,
                    "qty" => ($target > $item) ? $item : $target
                ]);
            }
            $target -= $item;
        }
        return $result;
    }
}
