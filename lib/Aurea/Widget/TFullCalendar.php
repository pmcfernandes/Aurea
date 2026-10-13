<?php
declare(strict_types=1);

namespace Aurea\Widget;

use Aurea\Core\TComponent;

/**
 * TFullCalendar — calendário interativo via FullCalendar.
 *
 *   $cal = new TFullCalendar;
 *   $cal->setEvents([['title' => 'Reunião', 'start' => '2026-10-20']]);
 *   $cal->show();
 *
 * @author Pedro Fernandes
 * @version 1.0.0
 * @license MIT
 */
class TFullCalendar extends TComponent
{
    protected static int $counter = 0;
    protected static bool $libIncluded = false;
    protected string $calendarId;
    protected array $events = [];
    protected string $eventsUrl = '';
    protected string $initialView = 'dayGridMonth';
    protected string $locale = 'pt';

    /**
     * Cria um calendário TFullCalendar vazio.
     */
    public function __construct()
    {
        parent::__construct('div');
        self::$counter++;
        $this->calendarId = 'calendar_' . self::$counter;
    }

    /**
     * Define os eventos a mostrar no calendário.
     *
     * @param array $events Lista de eventos com title, start e url/color opcionais
     * @return static
     */
    public function setEvents(array $events): static
    {
        $this->events = array_values($events);
        return $this;
    }

    /**
     * Define um feed JSON de eventos por URL.
     *
     * @param string $url URL do feed JSON de eventos
     * @return static
     */
    public function setEventsUrl(string $url): static
    {
        $this->eventsUrl = $url;
        return $this;
    }

    /**
     * Define a vista inicial do calendário.
     *
     * @param string $view Vista inicial (dayGridMonth, timeGridWeek, ...)
     * @return static
     */
    public function setInitialView(string $view): static
    {
        $this->initialView = $view;
        return $this;
    }

    /**
     * Define o idioma do calendário.
     *
     * @param string $locale Código do idioma
     * @return static
     */
    public function setLocale(string $locale = 'pt'): static
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Renderiza o HTML e o script de inicialização do calendário.
     *
     * @return string HTML do calendário
     */
    public function getContents(): string
    {
        $lib = '';
        if (!self::$libIncluded) {
            $lib = '<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>';
            self::$libIncluded = true;
        }
        $source = $this->eventsUrl !== '' ? $this->eventsUrl : $this->events;
        $config = json_encode([
            'initialView' => $this->initialView,
            'locale' => $this->locale,
            'events' => $source,
        ]);
        $attrs = $this->renderAttributes();
        return "{$lib}<div{$attrs}><div id='{$this->calendarId}'></div></div>"
             . "<script>new FullCalendar.Calendar(document.getElementById('{$this->calendarId}'), {$config}).render();</script>";
    }
}
