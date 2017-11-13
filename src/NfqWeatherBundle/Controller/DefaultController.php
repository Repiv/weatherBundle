<?php

namespace NfqWeatherBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use NfqWeatherBundle\Location;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $havana = new Location(23.1136, -82.3666);

        $weather = $this->get('nfq.weather')->fetch($havana);

        return $this->render('NfqWeatherBundle:Default:index.html.twig', ['weather' => $weather]);
    }
}
