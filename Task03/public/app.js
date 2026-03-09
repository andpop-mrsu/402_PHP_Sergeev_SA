const state = {
    currentGameId: null,
    currentPlayerName: '',
};

const startForm = document.getElementById('start-form');
const startMessage = document.getElementById('start-message');
const gameCard = document.getElementById('game-card');
const currentPlayer = document.getElementById('current-player');
const currentRound = document.getElementById('current-round');
const questionText = document.getElementById('question-text');
const answerForm = document.getElementById('answer-form');
const answerInput = document.getElementById('answer-input');
const answerButton = answerForm.querySelector('button[type="submit"]');
const stepFeedback = document.getElementById('step-feedback');
const historyList = document.getElementById('history-list');
const gameDetails = document.getElementById('game-details');
const refreshHistoryButton = document.getElementById('refresh-history');

async function api(url, options = {}) {
    const response = await fetch(url, {
        headers: {
            'Content-Type': 'application/json',
            ...(options.headers || {}),
        },
        ...options,
    });

    const data = await response.json();
    if (!response.ok) {
        throw new Error(data.error || 'Ошибка запроса к серверу.');
    }

    return data;
}

function setAnswerFormEnabled(enabled) {
    answerInput.disabled = !enabled;
    answerButton.disabled = !enabled;
}

function setCurrentQuestion(playerName, round, totalRounds, question) {
    currentPlayer.textContent = playerName;
    currentRound.textContent = `${round} / ${totalRounds}`;
    questionText.textContent = question;
    gameCard.hidden = false;
    setAnswerFormEnabled(true);
}

function renderHistory(games) {
    if (!games.length) {
        historyList.innerHTML = '<p class="muted">Пока нет сохранённых игр.</p>';
        return;
    }

    historyList.innerHTML = games.map((game) => {
        const status = game.isFinished
            ? `Завершена • ${game.correctAnswers}/${game.totalRounds}`
            : `В процессе • раунд ${game.currentRound}/${game.totalRounds}`;

        return `
            <button class="history-item" data-id="${game.id}" type="button">
                <span><strong>#${game.id}</strong> ${escapeHtml(game.playerName)}</span>
                <span class="muted">${status}</span>
            </button>
        `;
    }).join('');

    document.querySelectorAll('.history-item').forEach((button) => {
        button.addEventListener('click', () => loadGameDetails(button.dataset.id));
    });
}

function renderGameDetails(game) {
    const stepsHtml = game.steps.length
        ? `
            <div class="stack">
                ${game.steps.map((step) => `
                    <div class="step-item">
                        <div><strong>Раунд ${step.roundNumber}</strong></div>
                        <div>Числа: ${step.numbers.a} и ${step.numbers.b}</div>
                        <div>Ответ игрока: ${step.userAnswer}</div>
                        <div>Правильный ответ: ${step.correctAnswer}</div>
                        <div>${step.isCorrect ? 'Верно' : 'Неверно'}</div>
                    </div>
                `).join('')}
            </div>
        `
        : '<p class="muted">В этой игре ещё нет ходов.</p>';

    const currentQuestionHtml = game.isFinished || !game.currentQuestion
        ? ''
        : `
            <div class="notice">
                Текущий вопрос: ${escapeHtml(game.currentQuestion.question)}
            </div>
        `;

    gameDetails.innerHTML = `
        <div class="stack">
            <div><strong>Игра #${game.id}</strong></div>
            <div><strong>Игрок:</strong> ${escapeHtml(game.playerName)}</div>
            <div><strong>Результат:</strong> ${game.correctAnswers}/${game.totalRounds}</div>
            <div><strong>Статус:</strong> ${game.isFinished ? 'завершена' : 'в процессе'}</div>
            ${currentQuestionHtml}
            <h3>Ходы</h3>
            ${stepsHtml}
        </div>
    `;
}

async function loadHistory() {
    try {
        const data = await api('/games');
        renderHistory(data.games);
    } catch (error) {
        historyList.innerHTML = `<p class="error">${escapeHtml(error.message)}</p>`;
    }
}

async function loadGameDetails(id) {
    if (!id) {
        return;
    }

    try {
        const game = await api(`/games/${id}`);
        renderGameDetails(game);
    } catch (error) {
        gameDetails.innerHTML = `<p class="error">${escapeHtml(error.message)}</p>`;
    }
}

startForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    startMessage.textContent = '';
    stepFeedback.textContent = '';

    const playerName = document.getElementById('player-name').value.trim();
    if (!playerName) {
        startMessage.textContent = 'Введите имя игрока.';
        return;
    }

    try {
        const game = await api('/games', {
            method: 'POST',
            body: JSON.stringify({ playerName }),
        });

        state.currentGameId = game.id;
        state.currentPlayerName = game.playerName;
        setCurrentQuestion(game.playerName, game.currentRound, game.totalRounds, game.question);
        startMessage.textContent = `Игра #${game.id} создана.`;
        answerInput.value = '';
        answerInput.focus();
        await loadHistory();
        await loadGameDetails(game.id);
    } catch (error) {
        startMessage.textContent = error.message;
    }
});

answerForm.addEventListener('submit', async (event) => {
    event.preventDefault();

    if (!state.currentGameId) {
        stepFeedback.textContent = 'Сначала начни новую игру.';
        return;
    }

    const answerValue = answerInput.value.trim();
    if (answerValue === '') {
        stepFeedback.textContent = 'Введите ответ.';
        return;
    }

    try {
        const activeGameId = state.currentGameId;
        const result = await api(`/step/${activeGameId}`, {
            method: 'POST',
            body: JSON.stringify({ answer: Number(answerValue) }),
        });

        const step = result.step;
        const verdict = step.isCorrect ? 'Верно' : 'Неверно';
        stepFeedback.innerHTML = `
            <strong>${verdict}</strong><br>
            Вопрос: НОД(${step.numbers.a}, ${step.numbers.b})<br>
            Твой ответ: ${step.userAnswer}<br>
            Правильный ответ: ${step.correctAnswer}
        `;

        if (result.status === 'continue') {
            setCurrentQuestion(
                state.currentPlayerName,
                result.nextQuestion.roundNumber,
                result.progress.totalRounds,
                result.nextQuestion.question,
            );
        } else {
            questionText.textContent = 'Игра завершена.';
            currentRound.textContent = `${result.summary.totalRounds} / ${result.summary.totalRounds}`;
            const finalText = result.summary.isWin
                ? 'Поздравляем! Все ответы верные.'
                : `Игра завершена. Верных ответов: ${result.summary.correctAnswers} из ${result.summary.totalRounds}.`;
            stepFeedback.innerHTML += `<div class="notice">${finalText}</div>`;
            state.currentGameId = null;
            setAnswerFormEnabled(false);
        }

        answerInput.value = '';
        await loadHistory();
        await loadGameDetails(activeGameId);
    } catch (error) {
        stepFeedback.textContent = error.message;
    }
});

refreshHistoryButton.addEventListener('click', loadHistory);

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

setAnswerFormEnabled(false);
loadHistory();
