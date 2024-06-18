<?php

class Settings
{
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getAllSettings()
    {
        $sql = "SELECT setting_key, setting_value FROM settings";
        $stmt = $this->db->query($sql);
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        return $settings;
    }

    public function getSetting($settingName)
    {
        $sql = "SELECT setting_value FROM settings WHERE setting_key = :settingName";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':settingName', $settingName);
        $stmt->execute();
        return $stmt->fetchColumn();
    }


    public function updateSetting($settingName, $settingValue)
    {
        $sql = "UPDATE settings SET setting_value = :settingValue WHERE setting_key = :settingName";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':settingValue', $settingValue);
        $stmt->bindParam(':settingName', $settingName);
        $stmt->execute();
    }
}
