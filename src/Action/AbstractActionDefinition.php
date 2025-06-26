<?php
   // src/Action/AbstractActionDefinition.php
   namespace Shipweb\LineConnect\Action;

   abstract class AbstractActionDefinition implements ActionDefinitionInterface
   {
       protected string $secret_prefix;
       protected object $event;
       protected int    $scenario_id;

       public function set_secret_prefix(string $secret_prefix): void
       {
           $this->secret_prefix = $secret_prefix;
       }
       public function set_event(object $event): void
       {
           $this->event = $event;
       }
       public function set_scenario_id(int $scenario_id): void
       {
           $this->scenario_id = $scenario_id;
       }
   }