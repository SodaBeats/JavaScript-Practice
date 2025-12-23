

const score = {
    wins: 0,
    losses: 0,
    ties: 0
}

document.querySelector('.js-rock').
    addEventListener('click',(e)=>{
        playGame(e);
    })
document.querySelector('.js-paper').
    addEventListener('click', (e)=>{
        playGame(e);
    })
document.querySelector('.js-scissors').
    addEventListener('click', (e)=>{
        playGame(e);
    })
document.querySelector('.auto-play').
    addEventListener('click',()=>{
        autoPlay();
    })

//get computer choice and save it on a variable
function getComputerChoice(){
    const choices = ['rock', 'paper', 'scissors'];
    return choices[Math.floor(Math.random()*choices.length)];
}

//game function
function rockPaperScissors(arg1, arg2){
        //game logic
    if (arg1 === arg2){
        score.ties++;
        document.querySelector('.js-result').textContent = `You chose ${arg1}. 
            Computer chose ${arg2}. It's a tie!`;
    }
    else if(arg1 === 'rock' && arg2 === 'scissors' ||
            arg1 === 'paper' && arg2 === 'rock' ||
            arg1 === 'scissors' && arg2 === 'paper'){
        score.wins++;
        document.querySelector('.js-result').textContent = `You chose ${arg1}. 
            Computer chose ${arg2}. You win!`;
    }
    else {
        score.losses++;
        document.querySelector('.js-result').textContent = `You chose ${arg1}. 
            Computer chose ${arg2}. You lose!`;
    }
}

function playGame(event){

    const playerChoice = event.target.value;//check player choice
    const computerChoice = getComputerChoice();//comp choice
    rockPaperScissors(playerChoice, computerChoice);

    const scoreDisplay = document.querySelector('.js-score');//get score display variable

    scoreDisplay.textContent = `Wins: ${score.wins}, Losses: ${score.losses}, Ties: ${score.ties}`;

}
function resetScore(){
    const resetConfirm = document.querySelector('.js-reset-confirmation');
    
    score.wins = 0;
    score.losses = 0;
    score.ties = 0;
    document.querySelector('.js-score').textContent = '';
    document.querySelector('.js-result').textContent = '';
}

const playBtn = document.querySelector('.auto-play');
let isAutoPlaying = false;
let intervalId;

function autoPlay(){

    if(!isAutoPlaying){
        playBtn.textContent="Auto Playing...";
        intervalId = setInterval(()=>{
            const playerChoice = getComputerChoice();
            const computerChoice = getComputerChoice();
            rockPaperScissors(playerChoice, computerChoice);

            const scoreDisplay = document.querySelector('.js-score');//get score display variable

            scoreDisplay.textContent = `Wins: ${score.wins}, Losses: ${score.losses}, Ties: ${score.ties}`;
        },1000);
        isAutoPlaying = true;
    }
    else{
        playBtn.textContent = "Auto Play";
        clearInterval(intervalId);
        isAutoPlaying = false;
    }
}
