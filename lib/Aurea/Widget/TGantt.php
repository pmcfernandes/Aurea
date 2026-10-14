<?php
declare(strict_types=1);

namespace Aurea\Widget;

use Aurea\Core\TComponent;

/**
 * TGantt — Gantt leve e acessível para tarefas com início, fim e progresso.
 *
 * Não depende de JavaScript: os dados são renderizados como linhas semânticas
 * para que permaneçam legíveis em leitores de ecrã e impressão.
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TGantt extends TComponent
{
    /** @var array<int,array{label:string,start:string,end:string,progress?:float|int}> */
    private array $tasks = [];

    /** Cria um contentor de tarefas com semântica de lista. */
    public function __construct()
    {
        parent::__construct('div');
        $this->addClass('aurea-gantt');
        $this->setProperty('role', 'list');
    }

    /**
     * Define as tarefas do cronograma.
     *
     * @param array<int,array{label:string,start:string,end:string,progress?:float|int}> $tasks
     * @return static
     * @throws \InvalidArgumentException Quando uma tarefa não contiver datas válidas
     */
    public function setTasks(array $tasks): static
    {
        foreach ($tasks as $task) {
            if (!isset($task['label'], $task['start'], $task['end']) || strtotime($task['start']) === false || strtotime($task['end']) === false) {
                throw new \InvalidArgumentException('Cada tarefa requer label, start e end em datas válidas.');
            }
        }
        $this->tasks = array_values($tasks);
        return $this;
    }

    /**
     * Renderiza as tarefas numa escala relativa entre a primeira e a última data.
     *
     * @return string HTML do Gantt
     */
    public function getContents(): string
    {
        if ($this->tasks === []) {
            return '<div' . $this->renderAttributes() . '></div>';
        }
        $starts = array_map(fn(array $task): int => strtotime($task['start']), $this->tasks);
        $ends = array_map(fn(array $task): int => strtotime($task['end']), $this->tasks);
        $min = min($starts);
        $span = max(1, max($ends) - $min);
        $rows = '';
        foreach ($this->tasks as $task) {
            $left = max(0, (strtotime($task['start']) - $min) / $span * 100);
            $width = max(1, (strtotime($task['end']) - strtotime($task['start'])) / $span * 100);
            $progress = min(100, max(0, (float) ($task['progress'] ?? 0)));
            $label = htmlspecialchars($task['label']);
            $rows .= "<div class='aurea-gantt-row' role='listitem'><span>{$label}</span><div class='aurea-gantt-track'><div class='aurea-gantt-task' style='margin-left:{$left}%;width:{$width}%' aria-label='{$label}: {$progress}% concluído'><div style='width:{$progress}%'></div></div></div></div>";
        }
        return '<div' . $this->renderAttributes() . ">'{$rows}</div>";
    }
}
