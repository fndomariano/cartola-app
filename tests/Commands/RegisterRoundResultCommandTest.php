<?php 

namespace Tests\Commands;

use App\Console\Commands\RegisterRoundResultCommand;
use App\Models\RoundResult;
use App\Services\RoundResultService;
use App\Services\CartolaAPIService;
use Mockery;
use Tests\TestCase;

class RegisterRoundResultCommandTest extends TestCase
{     
    private const COMMAND_NAME = 'round-result:register';
    private const LEAGUE_OPTION = '--league';
    private const ROUND_OPTION = '--round';

    public function setUp() : void
    {
        parent::setUp();
    }

    public function tearDown() : void
    {
        parent::tearDown();
    }

    public function test_that_command_must_register_round_result() : void
    {
        $round = 10;
        $leagueSlug = 'cartolas-da-ruindade';
        
        $cartolaApiService = Mockery::mock(CartolaAPIService::class)->makePartial();
        
        $roundResultService = Mockery::mock(RoundResultService::class);  

        $roundResultService
            ->shouldReceive('setCartolaApiService')
            ->with($cartolaApiService)
            ->once()
            ->andReturnSelf();
        
        $roundResultService
            ->shouldReceive('register')
            ->with($round, $leagueSlug)
            ->once()
            ->andReturnNull();            
        
            
        $this->app->instance(CartolaAPIService::class, $cartolaApiService);
        $this->app->instance(RoundResultService::class, $roundResultService);
            
        $command = sprintf(
            "%s %s=%s %s=%s", 
            self::COMMAND_NAME, 
            self::LEAGUE_OPTION, $leagueSlug, 
            self::ROUND_OPTION, $round
        );

        $this->artisan($command)
            ->expectsOutput('Round result was registered successfully!')
            ->assertExitCode(RegisterRoundResultCommand::SUCCESS);
    }

    public function test_that_command_must_validate_league_option_required()
    {
        $command = sprintf("%s %s=%s", self::COMMAND_NAME, self::LEAGUE_OPTION, null);
        
        $this->artisan($command)
            ->expectsOutput('The league field is required.')
            ->assertExitCode(RegisterRoundResultCommand::INVALID);
        
    }

    public function test_that_command_must_validate_round_option_min_value()
    {
        $command = sprintf("%s %s=%s", self::COMMAND_NAME, self::ROUND_OPTION, 0);
        
        $this->artisan($command)
            ->expectsOutput('The round field must be at least 1.')
            ->assertExitCode(RegisterRoundResultCommand::INVALID);        
    }

    public function test_that_command_must_validate_round_option_max_value()
    {
        $command = sprintf("%s %s=%s", self::COMMAND_NAME, self::ROUND_OPTION, 39);
        
        $this->artisan($command)
            ->expectsOutput('The round field must not be greater than 38.')
            ->assertExitCode(RegisterRoundResultCommand::INVALID);        
    }
    
}