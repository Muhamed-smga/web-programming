const express = require('express');
const cors = require('cors');

const app = express();
const PORT = process.env.PORT || 3001;

app.use(cors());
app.use(express.json());

const agentState = {
  name: 'Task Agent',
  role: 'Personal productivity assistant',
  history: []
};

function buildAgentReply(message) {
  const lowerMessage = message.toLowerCase();

  if (lowerMessage.includes('hello') || lowerMessage.includes('hi')) {
    return 'Hello! I am your agent. Tell me what you want to plan, create, or review.';
  }

  if (lowerMessage.includes('todo') || lowerMessage.includes('task')) {
    return 'Here is a simple plan: 1) define the goal, 2) break it into steps, 3) schedule the highest-priority work, and 4) review progress.';
  }

  if (lowerMessage.includes('help')) {
    return 'I can help summarize ideas, plan tasks, review work, and suggest next steps.';
  }

  if (lowerMessage.includes('status') || lowerMessage.includes('progress')) {
    return 'The project is active and moving forward. Keep the scope focused and validate the result before shipping it.';
  }

  return `I processed your message: "${message}". The next useful step is to turn it into an actionable plan with clear milestones and a review checkpoint.`;
}

app.get('/api/health', (_req, res) => {
  res.json({
    ok: true,
    agent: agentState.name,
    role: agentState.role,
    timestamp: new Date().toISOString()
  });
});

app.post('/api/chat', (req, res) => {
  const message = String(req.body?.message || '').trim();

  if (!message) {
    return res.status(400).json({
      ok: false,
      error: 'Message is required.'
    });
  }

  const reply = buildAgentReply(message);
  const record = {
    prompt: message,
    response: reply,
    createdAt: new Date().toISOString()
  };

  agentState.history.push(record);

  res.json({
    ok: true,
    agent: agentState.name,
    response: reply,
    historyLength: agentState.history.length
  });
});

app.listen(PORT, () => {
  console.log(`Agent API listening on http://localhost:${PORT}`);
});
